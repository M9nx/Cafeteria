<?php

declare(strict_types=1);

namespace Cafeteria\Domain\Users;

final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly Role $role,
        public readonly bool $isActive,
        public readonly ?int $roomId = null,
        public readonly ?string $extension = null,
        public readonly ?string $profileImagePath = null,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            name: (string) $row['name'],
            email: (string) $row['email'],
            role: Role::fromString((string) $row['role']),
            isActive: (bool) ($row['is_active'] ?? true),
            roomId: isset($row['room_id']) ? (int) $row['room_id'] : null,
            extension: isset($row['extension']) ? (string) $row['extension'] : null,
            profileImagePath: isset($row['profile_image_path'])
                ? (string) $row['profile_image_path']
                : null,
        );
    }

    public function isAdmin(): bool
    {
        return $this->role->isAdmin();
    }
}
