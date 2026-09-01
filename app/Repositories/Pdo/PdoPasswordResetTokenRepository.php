<?php

declare(strict_types=1);

namespace Cafeteria\Repositories\Pdo;

use Cafeteria\Repositories\Contracts\PasswordResetTokenRepositoryInterface;
use DateTimeImmutable;
use PDO;

final class PdoPasswordResetTokenRepository implements PasswordResetTokenRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {
    }

    public function create(
        int $userId,
        string $tokenHash,
        DateTimeImmutable $expiresAt,
    ): int {
        $statement = $this->pdo->prepare(
            'INSERT INTO password_reset_tokens (
                user_id,
                token_hash,
                expires_at
            ) VALUES (
                :user_id,
                :token_hash,
                :expires_at
            )'
        );

        $statement->execute([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findValidByHash(string $tokenHash): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                id,
                user_id,
                token_hash,
                expires_at,
                used_at,
                created_at
             FROM password_reset_tokens
             WHERE token_hash = :token_hash
               AND used_at IS NULL
               AND expires_at > UTC_TIMESTAMP()
             LIMIT 1'
        );

        $statement->execute([
            'token_hash' => $tokenHash,
        ]);

        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function markUsed(int $tokenId): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE password_reset_tokens
             SET used_at = UTC_TIMESTAMP()
             WHERE id = :id
               AND used_at IS NULL'
        );

        $statement->execute([
            'id' => $tokenId,
        ]);

        return $statement->rowCount() === 1;
    }

    /**
     * Invalidate unused tokens by marking them as used.
     * Rows are retained for audit history instead of being deleted.
     */
    public function invalidateForUser(int $userId): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE password_reset_tokens
             SET used_at = UTC_TIMESTAMP()
             WHERE user_id = :user_id
               AND used_at IS NULL'
        );

        $statement->execute([
            'user_id' => $userId,
        ]);
    }
}