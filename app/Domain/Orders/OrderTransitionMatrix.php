<?php

declare(strict_types=1);

namespace Cafeteria\Domain\Orders;

final class OrderTransitionMatrix
{
    /**
     * Fulfillment transitions only. Cancellation uses a separate command path.
     *
     * @var array<string, list<string>>
     */
    private const FULFILLMENT_TRANSITIONS = [
        'PROCESSING' => ['OUT_FOR_DELIVERY'],
        'OUT_FOR_DELIVERY' => ['DONE'],
    ];

    public static function canTransition(string $fromStatus, string $toStatus): bool
    {
        $from = strtoupper(trim($fromStatus));
        $to = strtoupper(trim($toStatus));

        if ($from === '' || $to === '') {
            return false;
        }

        if ($to === OrderStatus::Cancelled->value) {
            return false;
        }

        $allowed = self::FULFILLMENT_TRANSITIONS[$from] ?? [];

        return in_array($to, $allowed, true);
    }

    /**
     * @return list<string>
     */
    public static function allowedNextStatuses(string $fromStatus): array
    {
        $from = strtoupper(trim($fromStatus));

        return self::FULFILLMENT_TRANSITIONS[$from] ?? [];
    }
}
