<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Policies\OrderPolicy;
use PHPUnit\Framework\TestCase;

final class OrderOwnershipPolicyTest extends TestCase
{
    private OrderPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new OrderPolicy();
    }

    public function test_user_cannot_view_another_users_order(): void
    {
        $user = $this->user(2);

        self::assertFalse(
            $this->policy->canViewOrder($user, 99)
        );
    }

    public function test_user_can_view_own_order(): void
    {
        $user = $this->user(2);

        self::assertTrue(
            $this->policy->canViewOrder($user, 2)
        );
    }

    public function test_user_cannot_cancel_another_users_processing_order(): void
    {
        $user = $this->user(2);

        self::assertFalse(
            $this->policy->canCancelOrder(
                $user,
                99,
                'PROCESSING'
            )
        );
    }

    public function test_user_can_cancel_own_processing_order(): void
    {
        $user = $this->user(2);

        self::assertTrue(
            $this->policy->canCancelOrder(
                $user,
                2,
                'PROCESSING'
            )
        );
    }

    public function test_user_cannot_cancel_order_that_is_not_processing(): void
    {
        $user = $this->user(2);

        self::assertFalse(
            $this->policy->canCancelOrder(
                $user,
                2,
                'DONE'
            )
        );
    }

    public function test_admin_can_view_another_users_order(): void
    {
        $admin = $this->user(1, 'ADMIN');

        self::assertTrue(
            $this->policy->canViewOrder($admin, 99)
        );
    }

    public function test_admin_can_cancel_another_users_processing_order(): void
    {
        $admin = $this->user(1, 'ADMIN');

        self::assertTrue(
            $this->policy->canCancelOrder(
                $admin,
                99,
                'PROCESSING'
            )
        );
    }

    private function user(int $id, string $role = 'USER'): AuthenticatedUser
    {
        return AuthenticatedUser::fromSession([
            'id' => $id,
            'email' => $role === 'ADMIN'
                ? 'admin@example.test'
                : 'user@example.test',
            'name' => $role === 'ADMIN'
                ? 'Demo Admin'
                : 'Demo User',
            'role' => $role,
        ]);
    }
}