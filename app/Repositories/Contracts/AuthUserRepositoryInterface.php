<?php

declare(strict_types=1);

namespace Cafeteria\Repositories\Contracts;

use Cafeteria\Domain\Users\User;

interface AuthUserRepositoryInterface
{
    /**
     * Find an active user record by normalized email for authentication.
     *
     * @return array{user: User, password_hash: string}|null
     */
    public function findActiveByEmail(string $email): ?array;

    public function updatePassword(int $userId, string $passwordHash): bool;
}
