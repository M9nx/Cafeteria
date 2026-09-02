<?php

declare(strict_types=1);

namespace Cafeteria\DTO;

final readonly class CreateProductRequest
{
    /**
     * @param array<string, mixed>|null $image
     */
    public function __construct(
        public string $name,
        public int $categoryId,
        public string $price,
        public bool $isAvailable,
        public ?array $image = null,
    ) {
    }
}