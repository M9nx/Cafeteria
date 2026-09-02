<?php

declare(strict_types=1);

namespace Cafeteria\DTO;

final readonly class OrderHistoryFilter
{
    public function __construct(
        public ?string $from,
        public ?string $to,
        public int $page = 1,
    ) {
    }
}