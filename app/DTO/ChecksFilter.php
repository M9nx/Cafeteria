<?php

declare(strict_types=1);

namespace Cafeteria\DTO;

final readonly class ChecksFilter
{
    public function __construct(
        public ?int $userId = null,
        public ?string $from = null,
        public ?string $to = null,
        public bool $includeCancelled = false,
    ) {
    }
}