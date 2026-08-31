<?php

declare(strict_types=1);

namespace Cafeteria\Repositories\Contracts;

interface PasswordResetTokenRepositoryInterface
{
    /**
     * Persist a hashed reset token. Never store the plain token.
     */
    public function create(
        int $userId,
        string $tokenHash,
        \DateTimeImmutable $expiresAt,
    ): int;

    /**
     * @return array<string, mixed>|null
     */
    public function findValidByHash(string $tokenHash): ?array;

    public function markUsed(int $tokenId): bool;

    public function invalidateForUser(int $userId): void;
}
