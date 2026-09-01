<?php

declare(strict_types=1);

namespace Cafeteria\DTO;

final readonly class LoginRequest
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            email: strtolower(trim((string) ($data['email'] ?? ''))),
            password: (string) ($data['password'] ?? ''),
        );
    }
}