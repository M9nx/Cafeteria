<?php

declare(strict_types=1);

namespace Cafeteria\Policies;

use Cafeteria\Core\Auth\AuthenticatedUser;

final class AdminPolicy
{
    public function canAccessAdminPanel(AuthenticatedUser $user): bool
    {
        return $user->isAdmin();
    }

    public function canManageUsers(AuthenticatedUser $user): bool
    {
        return $user->isAdmin();
    }

    public function canManageCategories(AuthenticatedUser $user): bool
    {
        return $user->isAdmin();
    }

    public function canManageProducts(AuthenticatedUser $user): bool
    {
        return $user->isAdmin();
    }
}
