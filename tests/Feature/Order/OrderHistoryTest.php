<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Domain\Users\Role;
use Cafeteria\DTO\OrderHistoryFilter;
use Cafeteria\Repositories\Contracts\OrderQueryRepositoryInterface;
use Cafeteria\Services\UserOrderQueryService;
use Cafeteria\Validation\OrderHistoryValidator;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class OrderHistoryTest extends TestCase
{
    private const OWNER_ID = 2;
    private const OTHER_USER_ID = 3;
    private const ADMIN_ID = 1;

    public function test_user_can_list_own_orders(): void
    {
        $orders = $this->ordersRepository();
        $service = $this->makeService($orders);

        $service->getUserWithOrders(
            self::OWNER_ID,
            new OrderHistoryFilter(from: null, to: null, page: 1),
            $this->user(self::OWNER_ID),
        );

        $call = $orders->lastPaginateCallArgs();
        self::assertNotNull($call);
        self::assertSame(self::OWNER_ID, $call['user_id']);
    }

    public function test_user_cannot_list_another_users_orders(): void
    {
        $orders = $this->ordersRepository();
        $service = $this->makeService($orders);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Forbidden.');

        try {
            $service->getUserWithOrders(
                self::OTHER_USER_ID,
                new OrderHistoryFilter(from: null, to: null, page: 1),
                $this->user(self::OWNER_ID),
            );
        } finally {
            self::assertNull(
                $orders->lastPaginateCallArgs(),
                'The repository must never be queried for a user the actor does not own (IDOR).'
            );
        }
    }

    public function test_admin_can_list_any_users_orders(): void
    {
        $orders = $this->ordersRepository();
        $service = $this->makeService($orders);

        $service->getUserWithOrders(
            self::OTHER_USER_ID,
            new OrderHistoryFilter(from: null, to: null, page: 1),
            $this->user(self::ADMIN_ID, 'ADMIN'),
        );

        $call = $orders->lastPaginateCallArgs();
        self::assertNotNull($call);
        self::assertSame(self::OTHER_USER_ID, $call['user_id']);
    }

    public function test_invalid_user_id_is_rejected(): void
    {
        $orders = $this->ordersRepository();
        $service = $this->makeService($orders);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User ID must be valid.');

        try {
            $service->getUserWithOrders(
                0,
                new OrderHistoryFilter(from: null, to: null, page: 1),
                $this->user(self::OWNER_ID),
            );
        } finally {
            self::assertNull($orders->lastPaginateCallArgs());
        }
    }

    public function test_invalid_date_format_is_rejected(): void
    {
        $orders = $this->ordersRepository();
        $service = $this->makeService($orders);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('From date must be in YYYY-MM-DD format.');

        try {
            $service->getUserWithOrders(
                self::OWNER_ID,
                new OrderHistoryFilter(from: 'not-a-date', to: null, page: 1),
                $this->user(self::OWNER_ID),
            );
        } finally {
            self::assertNull($orders->lastPaginateCallArgs());
        }
    }

    public function test_from_date_after_to_date_is_rejected(): void
    {
        $orders = $this->ordersRepository();
        $service = $this->makeService($orders);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('From date must not be after to date.');

        try {
            $service->getUserWithOrders(
                self::OWNER_ID,
                new OrderHistoryFilter(from: '2026-09-10', to: '2026-09-01', page: 1),
                $this->user(self::OWNER_ID),
            );
        } finally {
            self::assertNull($orders->lastPaginateCallArgs());
        }
    }

    public function test_page_below_one_is_rejected(): void
    {
        $orders = $this->ordersRepository();
        $service = $this->makeService($orders);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Page number is invalid.');

        try {
            $service->getUserWithOrders(
                self::OWNER_ID,
                new OrderHistoryFilter(from: null, to: null, page: 0),
                $this->user(self::OWNER_ID),
            );
        } finally {
            self::assertNull($orders->lastPaginateCallArgs());
        }
    }

    public function test_valid_range_reaches_repository_with_inclusive_end_of_day(): void
    {
        $orders = $this->ordersRepository();
        $service = $this->makeService($orders);

        $service->getUserWithOrders(
            self::OWNER_ID,
            new OrderHistoryFilter(from: '2026-09-01', to: '2026-09-01', page: 1),
            $this->user(self::OWNER_ID),
        );

        $call = $orders->lastPaginateCallArgs();
        self::assertNotNull($call);
        self::assertSame('2026-09-01 00:00:00', $call['from']->format('Y-m-d H:i:s'));
        self::assertSame(
            '2026-09-01 23:59:59',
            $call['to']->format('Y-m-d H:i:s'),
            'The "to" date must be normalized to end-of-day so same-day filters are inclusive.'
        );
    }

    private function makeService(OrderQueryRepositoryInterface $orders): UserOrderQueryService
    {
        $timezone = new DateTimeZone('UTC');

        return new UserOrderQueryService(
            $orders,
            new OrderHistoryValidator($timezone),
            $timezone,
        );
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

    private function ordersRepository(): FakeOrderQueryRepositoryForHistory
    {
        return new FakeOrderQueryRepositoryForHistory();
    }
}

final class FakeOrderQueryRepositoryForHistory implements OrderQueryRepositoryInterface
{
    /**
     * @var array{
     *     user_id: int,
     *     from: ?DateTimeImmutable,
     *     to: ?DateTimeImmutable,
     *     page: int,
     *     per_page: int
     * }|null
     */
    private ?array $lastPaginateCallArgs = null;

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
        $this->lastPaginateCallArgs = [
            'user_id' => $userId,
            'from' => $from,
            'to' => $to,
            'page' => $page,
            'per_page' => $perPage,
        ];

        return ['items' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage];
    }

    public function findOwnedDetail(int $orderId, int $userId): ?array
    {
        return null;
    }

    public function findDetailForAdmin(int $orderId): ?array
    {
        return null;
    }

    public function listCurrentQueue(int $page = 1, int $perPage = 15): array
    {
        return ['items' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage];
    }

    /**
     * @return array{
     *     user_id: int,
     *     from: ?DateTimeImmutable,
     *     to: ?DateTimeImmutable,
     *     page: int,
     *     per_page: int
     * }|null
     */
    public function lastPaginateCallArgs(): ?array
    {
        return $this->lastPaginateCallArgs;
    }
}