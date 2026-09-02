<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Domain\Users\Role;
use Cafeteria\DTO\OrderItemInput;
use Cafeteria\DTO\PlaceOrderRequest;
use Cafeteria\Repositories\Contracts\OrderCommandRepositoryInterface;
use Cafeteria\Repositories\Contracts\ProductRepositoryInterface;
use Cafeteria\Services\OrderService;
use Cafeteria\Validation\PlaceOrderValidator;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class OrderServiceTest extends TestCase
{
    public function test_place_recalculates_total_and_persists_snapshots(): void
    {
        $pdo = $this->sqliteWithRoom();
        $products = new FakeProductRepository([
            1 => ['name' => 'Tea', 'price' => '10.00'],
            2 => ['name' => 'Coffee', 'price' => '15.00'],
        ]);
        $orders = new RecordingOrderCommandRepository();
        $service = new OrderService(
            $products,
            $orders,
            new PlaceOrderValidator(),
            $pdo,
        );

        $orderId = $service->place(
            $this->user(),
            new PlaceOrderRequest(
                roomId: 1,
                notes: 'Desk delivery',
                items: [
                    new OrderItemInput(1, 2),
                    new OrderItemInput(2, 1),
                ],
            ),
        );

        self::assertSame(42, $orderId);
        self::assertSame('35.00', $orders->lastTotal());
        self::assertCount(2, $orders->lastItems());
        self::assertSame('Tea', $orders->lastItems()[0]['product_name_snapshot']);
        self::assertSame('10.00', $orders->lastItems()[0]['unit_price_snapshot']);
        self::assertSame('20.00', $orders->lastItems()[0]['line_total']);
    }

    public function test_place_rejects_unavailable_products(): void
    {
        $pdo = $this->sqliteWithRoom();
        $products = new FakeProductRepository([
            1 => ['name' => 'Tea', 'price' => '10.00'],
        ]);
        $orders = new RecordingOrderCommandRepository();
        $service = new OrderService(
            $products,
            $orders,
            new PlaceOrderValidator(),
            $pdo,
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unavailable');

        $service->place(
            $this->user(),
            new PlaceOrderRequest(
                roomId: 1,
                notes: null,
                items: [new OrderItemInput(99, 1)],
            ),
        );
    }

    public function test_place_rolls_back_transaction_on_item_insert_failure(): void
    {
        $pdo = $this->sqliteWithRoom();
        $products = new FakeProductRepository([
            1 => ['name' => 'Tea', 'price' => '10.00'],
        ]);
        $orders = new FailingOrderCommandRepository();
        $service = new OrderService(
            $products,
            $orders,
            new PlaceOrderValidator(),
            $pdo,
        );

        try {
            $service->place(
                $this->user(),
                new PlaceOrderRequest(
                    roomId: 1,
                    notes: null,
                    items: [new OrderItemInput(1, 1)],
                ),
            );

            self::fail('Expected order placement to fail.');
        } catch (RuntimeException $exception) {
            self::assertSame(
                'Simulated item insert failure',
                $exception->getMessage()
            );
        }

        self::assertFalse($pdo->inTransaction());
    }

    private function sqliteWithRoom(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE rooms (id INTEGER PRIMARY KEY)');
        $pdo->exec('INSERT INTO rooms (id) VALUES (1)');

        return $pdo;
    }

    private function user(): AuthenticatedUser
    {
        return new AuthenticatedUser(
            2,
            'user@example.test',
            'Demo User',
            Role::User,
        );
    }
}

final class FakeProductRepository implements ProductRepositoryInterface
{
    /**
     * @param array<int, array<string, mixed>> $products
     */
    public function __construct(
        private readonly array $products,
    ) {
    }

    public function paginate(int $page = 1, int $perPage = 15): array
    {
        return ['items' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage];
    }

    public function paginateAvailable(int $page = 1, int $perPage = 15): array
{
    return [
        'items' => [],
        'total' => 0,
        'page' => $page,
        'per_page' => $perPage,
    ];
}

    public function findById(int $id): ?array
    {
        return $this->products[$id] ?? null;
    }

    public function findAvailableByIds(array $ids): array
    {
        $items = [];

        foreach ($ids as $id) {
            if (isset($this->products[$id])) {
                $items[$id] = $this->products[$id];
            }
        }

        return $items;
    }

    public function create(array $attributes): int
    {
        return 0;
    }

    public function update(int $id, array $attributes): bool
    {
        return false;
    }

    public function softDelete(int $id): bool
    {
        return false;
    }
}

class RecordingOrderCommandRepository implements OrderCommandRepositoryInterface
{
    private ?string $lastTotal = null;

    /** @var list<array<string, mixed>> */
    private array $lastItems = [];

    public function insertOrder(
        int $userId,
        int $createdByUserId,
        int $roomId,
        ?string $notes,
        string $totalAmount,
    ): int {
        $this->lastTotal = $totalAmount;

        return 42;
    }

    public function insertItems(int $orderId, array $items): void
    {
        $this->lastItems = $items;
    }

    public function cancelIfProcessing(
        int $orderId,
        int $changedByUserId,
        \DateTimeImmutable $cancelledAt,
    ): bool {
        return false;
    }

    public function transitionIfCurrent(
        int $orderId,
        string $fromStatus,
        string $toStatus,
        int $changedByUserId,
        \DateTimeImmutable $changedAt,
    ): bool {
        return false;
    }

    public function lastTotal(): ?string
    {
        return $this->lastTotal;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function lastItems(): array
    {
        return $this->lastItems;
    }
}

final class FailingOrderCommandRepository extends RecordingOrderCommandRepository
{
    public function insertItems(int $orderId, array $items): void
    {
        throw new RuntimeException('Simulated item insert failure');
    }
}
