<?php

declare(strict_types=1);

namespace Cafeteria\DTO;

final readonly class ResetPasswordRequest
{
    public function __construct(
        public string $token,
        public string $password,
        public string $passwordConfirmation,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            token: trim((string) ($data['token'] ?? '')),
            password: (string) ($data['password'] ?? ''),
            passwordConfirmation: (string) ($data['password_confirmation'] ?? ''),
        );
    }
}