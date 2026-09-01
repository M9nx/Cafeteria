<?php

declare(strict_types=1);

namespace Cafeteria\Services;

use Cafeteria\Core\Session\SessionManager;
use Cafeteria\Repositories\Contracts\AuthUserRepositoryInterface;
use Cafeteria\Repositories\Contracts\PasswordResetTokenRepositoryInterface;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use RuntimeException;

final class PasswordResetService
{
    public function __construct(
        private readonly AuthUserRepositoryInterface $users,
        private readonly PasswordResetTokenRepositoryInterface $tokens,
        private readonly SessionManager $session,
        private readonly PDO $pdo,
        private readonly array $appConfig,
    ) {
    }

    /**
     * Request a password reset.
     *
     * Always returns a generic result so callers cannot discover
     * whether an email is registered.
     */
    public function requestReset(string $email): ?string
    {
        $email = strtolower(trim($email));

        $record = $this->users->findActiveByEmail($email);

        if (
            $record === null
            || !isset($record['user'])
        ) {
            return null;
        }

        $user = $record['user'];

        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $plainToken);

        $ttlMinutes = (int) ($this->appConfig['reset_token_ttl_minutes'] ?? 60);

        $expiresAt = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
            ->add(new DateInterval("PT{$ttlMinutes}M"));

        $this->pdo->beginTransaction();

        try {
            $this->tokens->invalidateForUser($user->id);

            $this->tokens->create(
                $user->id,
                $tokenHash,
                $expiresAt,
            );

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();

            throw $exception;
        }

        $appUrl = rtrim(
            (string) ($this->appConfig['url'] ?? ''),
            '/'
        );

        return "{$appUrl}/reset-password?token={$plainToken}";
    }

    /**
     * Complete a password reset using a valid token.
     */
    public function resetPassword(string $token, string $password): void
    {
        $token = trim($token);

        if ($token === '') {
            throw new RuntimeException('Invalid or expired reset token.');
        }

        $tokenHash = hash('sha256', $token);

        $tokenRecord = $this->tokens->findValidByHash($tokenHash);

        if ($tokenRecord === null) {
            throw new RuntimeException('Invalid or expired reset token.');
        }

        if (
            !isset($tokenRecord['id'], $tokenRecord['user_id'])
            || !is_numeric($tokenRecord['id'])
            || !is_numeric($tokenRecord['user_id'])
        ) {
            throw new RuntimeException('Invalid or expired reset token.');
        }

        $userId = (int) $tokenRecord['user_id'];
        $tokenId = (int) $tokenRecord['id'];

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        if ($passwordHash === false) {
            throw new RuntimeException('Unable to update password.');
        }

        $this->pdo->beginTransaction();

        try {
            $updated = $this->users->updatePassword(
                $userId,
                $passwordHash,
            );

            if (!$updated) {
                throw new RuntimeException('Unable to update password.');
            }

            $markedUsed = $this->tokens->markUsed($tokenId);

            if (!$markedUsed) {
                throw new RuntimeException('Invalid or expired reset token.');
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();

            throw $exception;
        }

        $this->session->destroy();
    }
}