<?php

declare(strict_types=1);

namespace Cafeteria\Tests\Unit\Policies;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Domain\Users\Role;
use Cafeteria\Policies\OrderPolicy;
use PHPUnit\Framework\TestCase;

final class OrderPolicyTest extends TestCase
{
    private OrderPolicy $policy;

    protected function setUp(): void
    {
        $this->policy = new OrderPolicy();
    }

    public function test_owner_can_view_own_order(): void
    {
        $user = new AuthenticatedUser(5, 'user@example.test', 'User', Role::User);

        self::assertTrue($this->policy->canViewOrder($user, 5));
    }

    public function test_user_cannot_view_another_users_order(): void
    {
        $user = new AuthenticatedUser(5, 'user@example.test', 'User', Role::User);

        self::assertFalse($this->policy->canViewOrder($user, 9));
    }

    public function test_admin_can_view_any_order(): void
    {
        $admin = new AuthenticatedUser(1, 'admin@example.test', 'Admin', Role::Admin);

        self::assertTrue($this->policy->canViewOrder($admin, 99));
    }

    public function test_owner_can_cancel_processing_order(): void
    {
        $user = new AuthenticatedUser(5, 'user@example.test', 'User', Role::User);

        self::assertTrue(
            $this->policy->canCancelOrder($user, 5, 'PROCESSING')
        );
    }

    public function test_owner_cannot_cancel_non_processing_order(): void
    {
        $user = new AuthenticatedUser(5, 'user@example.test', 'User', Role::User);

        self::assertFalse(
            $this->policy->canCancelOrder($user, 5, 'DONE')
        );
    }
}
