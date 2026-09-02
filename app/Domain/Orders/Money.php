<?php

declare(strict_types=1);

namespace Cafeteria\Domain\Orders;

use InvalidArgumentException;

final class Money
{
    private function __construct(
        private readonly int $cents,
    ) {
    }

    public static function fromString(string $amount): self
    {
        $normalized = trim($amount);

        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $normalized)) {
            throw new InvalidArgumentException(
                "Invalid money amount: {$amount}"
            );
        }

        if (!str_contains($normalized, '.')) {
            $normalized .= '.00';
        }

        [$whole, $fraction] = explode('.', $normalized, 2);
        $fraction = str_pad(substr($fraction, 0, 2), 2, '0');

        $cents = ((int) $whole * 100) + (int) $fraction;

        if ($cents <= 0) {
            throw new InvalidArgumentException(
                'Money amount must be greater than zero.'
            );
        }

        return new self($cents);
    }

    public function add(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function multiply(int $quantity): self
    {
        if ($quantity < 1) {
            throw new InvalidArgumentException(
                'Quantity must be at least 1.'
            );
        }

        return new self($this->cents * $quantity);
    }

    public function toString(): string
    {
        $whole = intdiv($this->cents, 100);
        $fraction = abs($this->cents % 100);

        return sprintf('%d.%02d', $whole, $fraction);
    }
}
