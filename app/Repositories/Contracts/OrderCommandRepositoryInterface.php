<?php

declare(strict_types=1);

namespace Cafeteria\Repositories\Contracts;

use DateTimeImmutable;

interface OrderCommandRepositoryInterface
{
    public function insertOrder(
        int $userId,
        int $createdByUserId,
        int $roomId,
        ?string $notes,
        string $totalAmount
    ): int;

    /**
     * @param list<array{
     *   product_id: int,
     *   product_name_snapshot: string,
     *   unit_price_snapshot: string,
     *   quantity: int,
     *   line_total: string
     * }> $items
     */
    public function insertItems(int $orderId, array $items): void;

    public function cancelIfProcessing(
        int $orderId,
        int $changedByUserId,
        DateTimeImmutable $cancelledAt
    ): bool;

    public function transitionIfCurrent(
        int $orderId,
        string $fromStatus,
        string $toStatus,
        int $changedByUserId,
        DateTimeImmutable $changedAt
    ): bool;
}
