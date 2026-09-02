<?php

declare(strict_types=1);

namespace Cafeteria\Services;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Domain\Orders\OrderTransitionMatrix;
use Cafeteria\Policies\OrderPolicy;
use Cafeteria\Repositories\Contracts\OrderCommandRepositoryInterface;
use Cafeteria\Repositories\Contracts\OrderQueryRepositoryInterface;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

final class OrderStatusService
{
    public function __construct(
        private readonly OrderQueryRepositoryInterface $orders,
        private readonly OrderCommandRepositoryInterface $commands,
        private readonly OrderPolicy $policy,
    ) {
    }

    public function cancel(AuthenticatedUser $actor, int $orderId): void
    {
        $order = $this->findOrderForActor($actor, $orderId);

        if ($order === null) {
            throw new RuntimeException('Order not found.');
        }

        $ownerUserId = (int) ($order['user_id'] ?? 0);
        $status = (string) ($order['status'] ?? '');

        if (!$this->policy->canCancelOrder($actor, $ownerUserId, $status)) {
            throw new RuntimeException('You are not allowed to cancel this order.');
        }

        $cancelled = $this->commands->cancelIfProcessing(
            $orderId,
            $actor->id(),
            new DateTimeImmutable(),
        );

        if (!$cancelled) {
            throw new InvalidArgumentException(
                'This order cannot be cancelled in its current state.'
            );
        }
    }

    public function transition(
        AuthenticatedUser $actor,
        int $orderId,
        string $nextStatus,
    ): void {
        if (!$actor->isAdmin()) {
            throw new RuntimeException('Forbidden.');
        }

        $order = $this->orders->findDetailForAdmin($orderId);

        if ($order === null) {
            throw new RuntimeException('Order not found.');
        }

        $currentStatus = (string) ($order['status'] ?? '');
        $normalizedNext = strtoupper(trim($nextStatus));

        if (!$this->policy->canTransitionOrder(
            $actor,
            $currentStatus,
            $normalizedNext,
        )) {
            throw new RuntimeException(
                'You are not allowed to apply this status change.'
            );
        }

        if (!OrderTransitionMatrix::canTransition($currentStatus, $normalizedNext)) {
            throw new InvalidArgumentException(
                'Invalid order status transition.'
            );
        }

        $updated = $this->commands->transitionIfCurrent(
            $orderId,
            $currentStatus,
            $normalizedNext,
            $actor->id(),
            new DateTimeImmutable(),
        );

        if (!$updated) {
            throw new InvalidArgumentException(
                'The order status has already changed. Please refresh and try again.'
            );
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findOrderForActor(
        AuthenticatedUser $actor,
        int $orderId,
    ): ?array {
        if ($actor->isAdmin()) {
            return $this->orders->findDetailForAdmin($orderId);
        }

        return $this->orders->findOwnedDetail($orderId, $actor->id());
    }
}
