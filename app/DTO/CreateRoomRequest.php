<?php

declare(strict_types=1);

namespace Cafeteria\DTO;

final readonly class CreateRoomRequest
{
    public function __construct(
        public string $name,
    ) {
    }
}
