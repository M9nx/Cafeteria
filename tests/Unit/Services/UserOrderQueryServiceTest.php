<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

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

final class UserOrderQueryServiceTest extends TestCase
{
    public function test_user_cannot_load_another_users_history(): void
    {
        $service = $this->makeService();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Forbidden.');

        $service->getUserWithOrders(
            9,
            new OrderHistoryFilter(null, null),
            $this->user(5),
        );
    }

    public function test_admin_can_load_any_users_history(): void
    {
        $repository = new UserHistoryOrderQueryRepository();
        $service = $this->makeService($repository);

        $result = $service->getUserWithOrders(
            9,
            new OrderHistoryFilter(null, null),
            $this->user(1, Role::Admin),
        );

        self::assertSame(9, $repository->lastUserId);
        self::assertSame(['items' => [], 'total' => 0, 'page' => 1, 'per_page' => 15], $result);
    }

    public function test_invalid_history_filter_raises_validation_error(): void
    {
        $service = $this->makeService();

        $this->expectException(InvalidArgumentException::class);

        $service->getUserWithOrders(
            5,
            new OrderHistoryFilter('not-a-date', null),
            $this->user(5),
        );
    }

    private function makeService(
        ?OrderQueryRepositoryInterface $repository = null,
    ): UserOrderQueryService {
        return new UserOrderQueryService(
            $repository ?? new UserHistoryOrderQueryRepository(),
            new OrderHistoryValidator(new DateTimeZone('Africa/Cairo')),
            new DateTimeZone('Africa/Cairo'),
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
final class UserHistoryOrderQueryRepository implements OrderQueryRepositoryInterface
{
    public ?int $lastUserId = null;

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
        $this->lastUserId = $userId;

        return ['items' => [], 'total' => 0, 'page' => 1, 'per_page' => $perPage];
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
        return ['items' => [], 'total' => 0, 'page' => 1, 'per_page' => $perPage];
    }
}
