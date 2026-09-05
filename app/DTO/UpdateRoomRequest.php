<?php

declare(strict_types=1);

namespace Cafeteria\DTO;

final readonly class UpdateRoomRequest
{
    public function __construct(
        public string $name,
        public bool $isActive,
    ) {
    }
}
