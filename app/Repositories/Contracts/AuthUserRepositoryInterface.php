<?php

declare(strict_types=1);

namespace Cafeteria\Repositories\Contracts;

interface AuthUserRepositoryInterface
{
    /**
     * Find an active user record by normalized email for authentication.
     *
     * @return array<string, mixed>|null
     */
    public function findActiveByEmail(string $email): ?array;

    public function updatePassword(int $userId, string $passwordHash): bool;
}
