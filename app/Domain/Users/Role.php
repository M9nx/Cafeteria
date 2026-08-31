<?php

declare(strict_types=1);

namespace Cafeteria\Domain\Users;

enum Role: string
{
    case User = 'USER';
    case Admin = 'ADMIN';

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }

    public static function fromString(string $value): self
    {
        return match (strtoupper(trim($value))) {
            'ADMIN' => self::Admin,
            'USER' => self::User,
            default => throw new \InvalidArgumentException(
                "Unsupported role value: {$value}"
            ),
        };
    }
}
