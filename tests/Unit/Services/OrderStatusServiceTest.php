<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

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

final class OrderStatusServiceTest extends TestCase
{
    public function test_user_can_cancel_own_processing_order(): void
    {
        $service = $this->makeService(
            order: [
                'id' => 10,
                'user_id' => 5,
                'status' => 'PROCESSING',
                'items' => [],
            ],
        );

        $service->cancel($this->user(5), 10);

        self::assertTrue(true);
    }

    public function test_user_cannot_cancel_someone_elses_order(): void
    {
        $service = $this->makeService(
            order: null,
        );

        $this->expectException(RuntimeException::class);

        $service->cancel($this->user(5), 10);
    }

    public function test_admin_transition_updates_status(): void
    {
        $commands = new StatusRecordingOrderCommandRepository();

        $service = new OrderStatusService(
            new FakeOrderQueryRepository([
                'id' => 10,
                'user_id' => 5,
                'status' => 'PROCESSING',
                'items' => [],
            ]),
            $commands,
            new OrderPolicy(),
        );

        $service->transition($this->user(1, Role::Admin), 10, 'OUT_FOR_DELIVERY');

        self::assertSame(
            ['PROCESSING', 'OUT_FOR_DELIVERY'],
            $commands->lastTransition,
        );
    }

    public function test_non_admin_cannot_transition(): void
    {
        $service = $this->makeService(
            order: [
                'id' => 10,
                'user_id' => 5,
                'status' => 'PROCESSING',
                'items' => [],
            ],
        );

        $this->expectException(RuntimeException::class);

        $service->transition($this->user(5), 10, 'OUT_FOR_DELIVERY');
    }

    public function test_stale_transition_raises_safe_error(): void
    {
        $commands = new StatusRecordingOrderCommandRepository(success: false);

        $service = new OrderStatusService(
            new FakeOrderQueryRepository([
                'id' => 10,
                'user_id' => 5,
                'status' => 'PROCESSING',
                'items' => [],
            ]),
            $commands,
            new OrderPolicy(),
        );

        $this->expectException(InvalidArgumentException::class);

        $service->transition($this->user(1, Role::Admin), 10, 'OUT_FOR_DELIVERY');
    }

    private function makeService(?array $order): OrderStatusService
    {
        return new OrderStatusService(
            new FakeOrderQueryRepository($order),
            new StatusRecordingOrderCommandRepository(),
            new OrderPolicy(),
        );
    }

    private function user(int $id, Role $role = Role::User): AuthenticatedUser
    {
        return new AuthenticatedUser(
            $id,
            $role === Role::Admin ? 'admin@example.test' : 'user@example.test',
            $role === Role::Admin ? 'Admin' : 'User',
            $role,
        );
    }
}

/** @implements OrderQueryRepositoryInterface */
final class FakeOrderQueryRepository implements OrderQueryRepositoryInterface
{
    public function __construct(
        private readonly ?array $order,
    ) {
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
        int $perPage = 15,
    ): array {
        return ['items' => [], 'total' => 0, 'page' => 1, 'per_page' => 15];
    }

    public function findOwnedDetail(int $orderId, int $userId): ?array
    {
        if ($this->order === null) {
            return null;
        }

        if ((int) ($this->order['user_id'] ?? 0) !== $userId) {
            return null;
        }

        return $this->order;
    }

    public function findDetailForAdmin(int $orderId): ?array
    {
        return $this->order;
    }

    public function listCurrentQueue(int $page = 1, int $perPage = 15): array
    {
        return ['items' => [], 'total' => 0, 'page' => 1, 'per_page' => 15];
    }
}

final class StatusRecordingOrderCommandRepository implements OrderCommandRepositoryInterface
{
    /** @var list<string>|null */
    public ?array $lastTransition = null;

    public function __construct(
        private readonly bool $success = true,
    ) {
    }

    public function insertOrder(
        int $userId,
        int $createdByUserId,
        int $roomId,
        ?string $notes,
        string $totalAmount,
    ): int {
        return 1;
    }

    public function insertItems(int $orderId, array $items): void
    {
    }

    public function cancelIfProcessing(
        int $orderId,
        int $changedByUserId,
        DateTimeImmutable $cancelledAt,
    ): bool {
        return $this->success;
    }

    public function transitionIfCurrent(
        int $orderId,
        string $fromStatus,
        string $toStatus,
        int $changedByUserId,
        DateTimeImmutable $changedAt,
    ): bool {
        if (!$this->success) {
            return false;
        }

        $this->lastTransition = [$fromStatus, $toStatus];

        return true;
    }
}
