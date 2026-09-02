<?php

declare(strict_types=1);

namespace Cafeteria\Domain\Orders;

use InvalidArgumentException;

final class OrderLine
{
    private readonly Money $lineTotal;

    private readonly string $productName;

    public function __construct(
        private readonly int $productId,
        string $productName,
        private readonly Money $unitPrice,
        private readonly int $quantity,
    ) {
        if ($quantity < 1) {
            throw new InvalidArgumentException(
                'Order line quantity must be at least 1.'
            );
        }

        $name = trim($productName);

        if ($name === '') {
            throw new InvalidArgumentException(
                'Order line product name is required.'
            );
        }

        $this->productName = $name;
        $this->lineTotal = $unitPrice->multiply($quantity);
    }

    public function productId(): int
    {
        return $this->productId;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function lineTotal(): Money
    {
        return $this->lineTotal;
    }

    /**
     * @return array{
     *   product_id: int,
     *   product_name_snapshot: string,
     *   unit_price_snapshot: string,
     *   quantity: int,
     *   line_total: string
     * }
     */
    public function toPersistenceArray(): array
    {
        return [
            'product_id' => $this->productId,
            'product_name_snapshot' => $this->productName,
            'unit_price_snapshot' => $this->unitPrice->toString(),
            'quantity' => $this->quantity,
            'line_total' => $this->lineTotal->toString(),
        ];
    }
}
