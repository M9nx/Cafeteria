<?php

declare(strict_types=1);

namespace Cafeteria\Policies;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Domain\Orders\OrderTransitionMatrix;

final class OrderPolicy
{
    public function canViewOrder(AuthenticatedUser $user, int $orderUserId): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id() === $orderUserId;
    }

    public function canCancelOrder(
        AuthenticatedUser $user,
        int $orderUserId,
        string $currentStatus,
    ): bool {
        if ($currentStatus !== 'PROCESSING') {
            return false;
        }

        return $this->canViewOrder($user, $orderUserId);
    }

    public function canTransitionOrder(
        AuthenticatedUser $user,
        string $currentStatus,
        string $nextStatus,
    ): bool {
        if (!$user->isAdmin()) {
            return false;
        }

        return OrderTransitionMatrix::canTransition(
            $currentStatus,
            $nextStatus,
        );
    }
}
