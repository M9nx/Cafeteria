<?php

declare(strict_types=1);

namespace Cafeteria\Services;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Domain\Orders\Money;
use Cafeteria\Domain\Orders\OrderLine;
use Cafeteria\DTO\OrderItemInput;
use Cafeteria\DTO\PlaceOrderOnBehalfRequest;
use Cafeteria\DTO\PlaceOrderRequest;
use Cafeteria\Repositories\Contracts\OrderCommandRepositoryInterface;
use Cafeteria\Repositories\Contracts\ProductRepositoryInterface;
use Cafeteria\Validation\PlaceOrderOnBehalfValidator;
use Cafeteria\Validation\PlaceOrderValidator;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

final class OrderService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly OrderCommandRepositoryInterface $orders,
        private readonly PlaceOrderValidator $validator,
        private readonly PlaceOrderOnBehalfValidator $onBehalfValidator,
        private readonly PDO $pdo,
    ) {
    }

    public function place(
        AuthenticatedUser $user,
        PlaceOrderRequest $request,
    ): int {
        $errors = $this->validator->validate($request);

        if ($errors !== []) {
            throw new InvalidArgumentException(
                implode(' ', $errors)
            );
        }

        $this->assertRoomExists($request->roomId);

        return $this->persistOrder(
            customerUserId: $user->id(),
            createdByUserId: $user->id(),
            roomId: $request->roomId,
            notes: $request->notes,
            items: $request->items,
        );
    }

    public function placeOnBehalf(
        AuthenticatedUser $admin,
        PlaceOrderOnBehalfRequest $request,
    ): int {
        if (!$admin->isAdmin()) {
            throw new InvalidArgumentException(
                'Only administrators can place orders on behalf of users.'
            );
        }

        $errors = $this->onBehalfValidator->validate($request);

        if ($errors !== []) {
            throw new InvalidArgumentException(
                implode(' ', $errors)
            );
        }

        $this->assertRoomExists($request->roomId);

        return $this->persistOrder(
            customerUserId: $request->userId,
            createdByUserId: $admin->id(),
            roomId: $request->roomId,
            notes: $request->notes,
            items: $request->items,
        );
    }

    /**
     * Shared pricing and persistence path for normal and admin-created orders.
     *
     * @param list<OrderItemInput> $items
     */
    private function persistOrder(
        int $customerUserId,
        int $createdByUserId,
        int $roomId,
        ?string $notes,
        array $items,
    ): int {
        $quantitiesByProduct = $this->aggregateQuantities($items);
        $productIds = array_keys($quantitiesByProduct);

        $availableProducts = $this->products->findAvailableByIds(
            $productIds
        );

        if (count($availableProducts) !== count($productIds)) {
            throw new InvalidArgumentException(
                'One or more selected products are unavailable.'
            );
        }

        $lines = [];
        $orderTotal = null;

        foreach ($quantitiesByProduct as $productId => $quantity) {
            $product = $availableProducts[$productId];

            $name = trim(
                (string) ($product['name'] ?? '')
            );

            if ($name === '') {
                throw new RuntimeException(
                    'Unable to resolve product details for ordering.'
                );
            }

            $unitPrice = Money::fromString(
                (string) ($product['price'] ?? '0')
            );

            $line = new OrderLine(
                productId: $productId,
                productName: $name,
                unitPrice: $unitPrice,
                quantity: $quantity,
            );

            $lines[] = $line;

            $orderTotal = $orderTotal === null
                ? $line->lineTotal()
                : $orderTotal->add($line->lineTotal());
        }

        if ($orderTotal === null) {
            throw new InvalidArgumentException(
                'Your cart must contain at least one item.'
            );
        }

        $persistedItems = array_map(
            static fn (OrderLine $line): array =>
                $line->toPersistenceArray(),
            $lines,
        );

        $this->pdo->beginTransaction();

        try {
            $orderId = $this->orders->insertOrder(
                userId: $customerUserId,
                createdByUserId: $createdByUserId,
                roomId: $roomId,
                notes: $notes,
                totalAmount: $orderTotal->toString(),
            );

            $this->orders->insertItems(
                $orderId,
                $persistedItems
            );

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }

        return $orderId;
    }

    /**
     * @param list<OrderItemInput> $items
     *
     * @return array<int, int>
     */
    private function aggregateQuantities(array $items): array
    {
        $quantities = [];

        foreach ($items as $item) {
            $productId = $item->productId;

            if (!isset($quantities[$productId])) {
                $quantities[$productId] = 0;
            }

            $quantities[$productId] += $item->quantity;
        }

        return $quantities;
    }

    private function assertRoomExists(int $roomId): void
    {
        $statement = $this->pdo->prepare(
            'SELECT id
             FROM rooms
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute([
            'id' => $roomId,
        ]);

        if ($statement->fetchColumn() === false) {
            throw new InvalidArgumentException(
                'Please select a valid room.'
            );
        }
    }
}