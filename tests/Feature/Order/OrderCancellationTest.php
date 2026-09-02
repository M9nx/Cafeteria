<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Domain\Users\Role;
use Cafeteria\Policies\OrderPolicy;
use Cafeteria\Repositories\Contracts\OrderCommandRepositoryInterface;
use Cafeteria\Repositories\Contracts\OrderQueryRepositoryInterface;
use Cafeteria\Services\OrderStatusService;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class OrderCancellationTest extends TestCase
{
    private const OWNER_ID = 2;
    private const OTHER_USER_ID = 3;
    private const ADMIN_ID = 1;
    private const PROCESSING_ORDER_ID = 501;
    private const DONE_ORDER_ID = 502;
    private const MISSING_ORDER_ID = 999;

    public function test_owner_can_cancel_own_processing_order(): void
    {
        $orders = $this->ordersRepository();
        $commands = $this->commandsRepository();
        $service = $this->makeService($orders, $commands);

        $service->cancel($this->user(self::OWNER_ID), self::PROCESSING_ORDER_ID);

        self::assertSame(
            [self::PROCESSING_ORDER_ID, self::OWNER_ID],
            $commands->lastCancelCallArgs(),
            'cancelIfProcessing() must be called with the order id and the acting user id.'
        );
    }

    public function test_user_cannot_cancel_another_users_order(): void
    {
        $orders = $this->ordersRepository();
        $commands = $this->commandsRepository();
        $service = $this->makeService($orders, $commands);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Order not found.');

        try {
            // Owned by OTHER_USER_ID, attacker is OWNER_ID.
            $service->cancel($this->user(self::OWNER_ID), self::OTHER_USERS_ORDER_ID());
        } finally {
            self::assertNull(
                $commands->lastCancelCallArgs(),
                'No cancellation write must be attempted for an order the actor does not own (IDOR).'
            );
        }
    }

    public function test_admin_can_cancel_another_users_processing_order(): void
    {
        $orders = $this->ordersRepository();
        $commands = $this->commandsRepository();
        $service = $this->makeService($orders, $commands);

        $service->cancel($this->user(self::ADMIN_ID, 'ADMIN'), self::PROCESSING_ORDER_ID);

        self::assertSame(
            [self::PROCESSING_ORDER_ID, self::ADMIN_ID],
            $commands->lastCancelCallArgs()
        );
    }

    public function test_cannot_cancel_order_that_is_not_processing(): void
    {
        $orders = $this->ordersRepository();
        $commands = $this->commandsRepository();
        $service = $this->makeService($orders, $commands);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You are not allowed to cancel this order.');

        try {
            $service->cancel($this->user(self::OWNER_ID), self::DONE_ORDER_ID);
        } finally {
            self::assertNull(
                $commands->lastCancelCallArgs(),
                'A DONE order must be rejected by policy before any repository write is attempted.'
            );
        }
    }

    public function test_cancelling_a_missing_order_reports_not_found(): void
    {
        $service = $this->makeService($this->ordersRepository(), $this->commandsRepository());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Order not found.');

        $service->cancel($this->user(self::OWNER_ID), self::MISSING_ORDER_ID);
    }

    public function test_repository_race_condition_surfaces_as_invalid_argument(): void
    {
        $orders = $this->ordersRepository();
        $commands = $this->commandsRepository();
        $commands->failNextCancelAttempt();

        $service = $this->makeService($orders, $commands);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'This order cannot be cancelled in its current state.'
        );

        $service->cancel($this->user(self::OWNER_ID), self::PROCESSING_ORDER_ID);
    }

    private function makeService(
        OrderQueryRepositoryInterface $orders,
        OrderCommandRepositoryInterface $commands,
    ): OrderStatusService {
        return new OrderStatusService($orders, $commands, new OrderPolicy());
    }

    private function user(int $id, string $role = 'USER'): AuthenticatedUser
    {
        return new AuthenticatedUser(
            $id,
            $role === 'ADMIN' ? 'admin@example.test' : 'owner@example.test',
            $role === 'ADMIN' ? 'Demo Admin' : 'Demo Owner',
            Role::fromString($role),
        );
    }

    private function ordersRepository(): FakeOrderQueryRepositoryForCancellation
    {
        return new FakeOrderQueryRepositoryForCancellation([
            self::PROCESSING_ORDER_ID => [
                'id' => self::PROCESSING_ORDER_ID,
                'user_id' => self::OWNER_ID,
                'status' => 'PROCESSING',
            ],
            self::DONE_ORDER_ID => [
                'id' => self::DONE_ORDER_ID,
                'user_id' => self::OWNER_ID,
                'status' => 'DONE',
            ],
            self::OTHER_USERS_ORDER_ID() => [
                'id' => self::OTHER_USERS_ORDER_ID(),
                'user_id' => self::OTHER_USER_ID,
                'status' => 'PROCESSING',
            ],
        ]);
    }

    private function commandsRepository(): FakeOrderCommandRepositoryForCancellation
    {
        return new FakeOrderCommandRepositoryForCancellation();
    }

    private static function OTHER_USERS_ORDER_ID(): int
    {
        return 503;
    }
}

final class FakeOrderQueryRepositoryForCancellation implements OrderQueryRepositoryInterface
{
    /**
     * @param array<int, array<string, mixed>> $orders
     */
    public function __construct(private array $orders)
    {
    }
    public function findLatestForUser(int $userId): ?array
    {
        return null;
    }
    public function paginateForUser(
        int $userId,
        ?DateTimeImmutable $from,
        ?DateTimeImmutable $to,
        int $page = 1,
        int $perPage = 15
    ): array {
        return ['items' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage];
    }

    public function findOwnedDetail(int $orderId, int $userId): ?array
    {
        $order = $this->orders[$orderId] ?? null;

        if ($order === null || (int) $order['user_id'] !== $userId) {
            return null;
        }

        return $order;
    }

    public function findDetailForAdmin(int $orderId): ?array
    {
        return $this->orders[$orderId] ?? null;
    }

    public function listCurrentQueue(int $page = 1, int $perPage = 15): array
    {
        return ['items' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage];
    }
}
final class FakeOrderCommandRepositoryForCancellation implements OrderCommandRepositoryInterface
{
    private bool $failNextCancel = false;

    /** @var array{0: int, 1: int}|null */
    private ?array $lastCancelCallArgs = null;

    public function insertOrder(
        int $userId,
        int $createdByUserId,
        int $roomId,
        ?string $notes,
        string $totalAmount,
    ): int {
        return 0;
    }
    public function insertItems(int $orderId, array $items): void
    {
    }
    public function cancelIfProcessing(
        int $orderId,
        int $changedByUserId,
        DateTimeImmutable $cancelledAt,
    ): bool {
        $this->lastCancelCallArgs = [$orderId, $changedByUserId];

        if ($this->failNextCancel) {
            $this->failNextCancel = false;

            return false;
        }

        return true;
    }
    public function transitionIfCurrent(
        int $orderId,
        string $fromStatus,
        string $toStatus,
        int $changedByUserId,
        DateTimeImmutable $changedAt,
    ): bool {
        return false;
    }

    public function failNextCancelAttempt(): void
    {
        $this->failNextCancel = true;
    }

    /**
     * @return array{0: int, 1: int}|null
     */
    public function lastCancelCallArgs(): ?array
    {
        return $this->lastCancelCallArgs;
    }
}