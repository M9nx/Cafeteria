<?php

declare(strict_types=1);

namespace Cafeteria\DTO;

final readonly class UpdateProductRequest
{
    public function __construct(
        public string $name,
        public int $categoryId,
        public string $price,
        public bool $isAvailable,
        public ?string $imagePath = null,
    ) {
    }
}