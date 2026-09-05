<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Domain\Users\Role;
use Cafeteria\Policies\AdminPolicy;
use PHPUnit\Framework\TestCase;

final class AdminPolicyTest extends TestCase
{
    private AdminPolicy $policy;

    protected function setUp(): void
    {
        $this->policy = new AdminPolicy();
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $admin = new AuthenticatedUser(
            1,
            'admin@example.test',
            'Admin',
            Role::Admin
        );

        self::assertTrue($this->policy->canAccessAdminPanel($admin));
    }

    public function test_regular_user_cannot_access_admin_panel(): void
    {
        $user = new AuthenticatedUser(
            5,
            'user@example.test',
            'User',
            Role::User
        );

        self::assertFalse($this->policy->canAccessAdminPanel($user));
    }

    public function test_admin_can_manage_users(): void
    {
        $admin = new AuthenticatedUser(
            1,
            'admin@example.test',
            'Admin',
            Role::Admin
        );

        self::assertTrue($this->policy->canManageUsers($admin));
    }

    public function test_regular_user_cannot_manage_users(): void
    {
        $user = new AuthenticatedUser(
            5,
            'user@example.test',
            'User',
            Role::User
        );

        self::assertFalse($this->policy->canManageUsers($user));
    }

    public function test_admin_can_manage_categories(): void
    {
        $admin = new AuthenticatedUser(
            1,
            'admin@example.test',
            'Admin',
            Role::Admin
        );

        self::assertTrue($this->policy->canManageCategories($admin));
    }

    public function test_regular_user_cannot_manage_categories(): void
    {
        $user = new AuthenticatedUser(
            5,
            'user@example.test',
            'User',
            Role::User
        );

        self::assertFalse($this->policy->canManageCategories($user));
    }

    public function test_admin_can_manage_rooms(): void
    {
        $admin = new AuthenticatedUser(
            1,
            'admin@example.test',
            'Admin',
            Role::Admin
        );

        self::assertTrue($this->policy->canManageRooms($admin));
    }

    public function test_regular_user_cannot_manage_rooms(): void
    {
        $user = new AuthenticatedUser(
            5,
            'user@example.test',
            'User',
            Role::User
        );

        self::assertFalse($this->policy->canManageRooms($user));
    }
}
