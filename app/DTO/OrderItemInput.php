<?php

declare(strict_types=1);

namespace Cafeteria\DTO;

final readonly class OrderItemInput
{
    public function __construct(
        public int $productId,
        public int $quantity,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            productId: (int) ($data['product_id'] ?? $data['productId'] ?? 0),
            quantity: (int) ($data['quantity'] ?? 0),
        );
    }
}
