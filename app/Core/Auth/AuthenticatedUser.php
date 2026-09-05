<?php

declare(strict_types=1);

namespace Cafeteria\Core\Auth;

use Cafeteria\Domain\Users\Role;

final readonly class AuthenticatedUser
{
    public function __construct(
        private int $id,
        private string $email,
        private string $name,
        private Role $role,
        private ?string $profileImagePath = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromSession(array $data): self
    {
        $profileImagePath = $data['profile_image_path'] ?? null;

        return new self(
            id: (int) $data['id'],
            email: (string) $data['email'],
            name: (string) $data['name'],
            role: Role::fromString((string) $data['role']),
            profileImagePath: is_string($profileImagePath) && $profileImagePath !== ''
                ? $profileImagePath
                : null,
        );
    }

    /**
     * @return array{
     *     id: int,
     *     email: string,
     *     name: string,
     *     role: string,
     *     profile_image_path: ?string
     * }
     */
    public function toSessionArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'role' => $this->role->value,
            'profile_image_path' => $this->profileImagePath,
        ];
    }

    public function id(): int
    {
        return $this->id;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function role(): Role
    {
        return $this->role;
    }

    public function profileImagePath(): ?string
    {
        return $this->profileImagePath;
    }

    public function isAdmin(): bool
    {
        return $this->role->isAdmin();
    }
}
