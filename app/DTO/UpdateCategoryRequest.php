<?php

declare(strict_types=1);

namespace Cafeteria\DTO;

final readonly class UpdateCategoryRequest
{
    public function __construct(
        public string $name,
        public bool $isActive,
    ) {
    }
}
