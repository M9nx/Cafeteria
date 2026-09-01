<?php

declare(strict_types=1);

namespace Cafeteria\DTO;

final readonly class CreateUserRequest
{
    /**
     * @param array<string, mixed>|null $image
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $role,
        public ?int $roomId,
        public ?string $extension,
        public string $password,
        public ?array $image = null,
    ) {
    }
}