<?php

declare(strict_types=1);

namespace Cafeteria\Domain\Orders;

enum OrderStatus: string
{
    case Processing = 'PROCESSING';
    case OutForDelivery = 'OUT_FOR_DELIVERY';
    case Done = 'DONE';
    case Cancelled = 'CANCELLED';

    public static function fromString(string $value): self
    {
        return match (strtoupper(trim($value))) {
            'PROCESSING' => self::Processing,
            'OUT_FOR_DELIVERY' => self::OutForDelivery,
            'DONE' => self::Done,
            'CANCELLED' => self::Cancelled,
            default => throw new \InvalidArgumentException(
                "Unsupported order status: {$value}"
            ),
        };
    }
}
