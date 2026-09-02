<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Cafeteria\Core\Auth\CsrfTokenManager;
use Cafeteria\Core\Database\ConnectionFactory;
use Tests\Support\HttpTestCase;

final class PasswordResetTest extends HttpTestCase
{
    private const EMAIL = 'user@example.test';
    private const DEV_PASSWORD = 'DevPassword123!';

    public function test_valid_reset_token_changes_password_and_redirects_to_login(): void
    {
        $pdo = $this->pdo();
        $token = $this->createResetToken($pdo, self::EMAIL);

        try {
            $response = $this->post('/reset-password', [
                'token' => $token,
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
                CsrfTokenManager::FIELD_NAME => $this->csrfToken(),
            ]);

            self::assertSame(302, $this->responseStatus($response));
            self::assertSame('/login', $this->responseHeader($response, 'Location'));

            $this->get('/login');

            $login = $this->post('/login', [
                'email' => self::EMAIL,
                'password' => 'NewPassword123!',
                CsrfTokenManager::FIELD_NAME => $this->csrfToken(),
            ]);

            self::assertSame(302, $this->responseStatus($login));
            self::assertSame('/', $this->responseHeader($login, 'Location'));
        } finally {
            $this->restorePassword($pdo, self::EMAIL);
        }
    }

    public function test_invalid_reset_token_is_rejected(): void
    {
        $response = $this->post('/reset-password', [
            'token' => 'invalid-reset-token',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
            CsrfTokenManager::FIELD_NAME => $this->csrfToken(),
        ]);

        self::assertSame(200, $this->responseStatus($response));

        self::assertStringContainsString(
            'Invalid or expired reset token.',
            $this->responseContent($response)
        );
    }

    public function test_expired_reset_token_is_rejected(): void
    {
        $pdo = $this->pdo();
        $token = bin2hex(random_bytes(32));

        $this->createResetToken(
            $pdo,
            self::EMAIL,
            $token,
            true
        );

        $response = $this->post('/reset-password', [
            'token' => $token,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
            CsrfTokenManager::FIELD_NAME => $this->csrfToken(),
        ]);

        self::assertSame(200, $this->responseStatus($response));

        self::assertStringContainsString(
            'Invalid or expired reset token.',
            $this->responseContent($response)
        );
    }

    public function test_used_reset_token_is_rejected(): void
    {
        $pdo = $this->pdo();
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);

        $userId = $this->userId($pdo, self::EMAIL);

        $statement = $pdo->prepare(
            'INSERT INTO password_reset_tokens
                (user_id, token_hash, expires_at, used_at)
             VALUES
                (:user_id, :token_hash,
                 UTC_TIMESTAMP() + INTERVAL 30 MINUTE,
                 UTC_TIMESTAMP())'
        );

        $statement->execute([
            'user_id' => $userId,
            'token_hash' => $hash,
        ]);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
            CsrfTokenManager::FIELD_NAME => $this->csrfToken(),
        ]);

        self::assertSame(200, $this->responseStatus($response));

        self::assertStringContainsString(
            'Invalid or expired reset token.',
            $this->responseContent($response)
        );
    }

    private function pdo(): \PDO
    {
        return (new ConnectionFactory())->make(
            require dirname(__DIR__, 3) . '/config/database.php'
        );
    }

    private function userId(\PDO $pdo, string $email): int
    {
        $statement = $pdo->prepare(
            'SELECT id FROM users WHERE email = :email LIMIT 1'
        );

        $statement->execute(['email' => $email]);

        $id = $statement->fetchColumn();

        self::assertNotFalse(
            $id,
            'Expected seeded demo user before testing password reset.'
        );

        return (int) $id;
    }

    private function createResetToken(
        \PDO $pdo,
        string $email,
        ?string $token = null,
        bool $expired = false
    ): string {
        $token ??= bin2hex(random_bytes(32));

        $hash = hash('sha256', $token);
        $userId = $this->userId($pdo, $email);

        $pdo->prepare(
            'UPDATE password_reset_tokens
            SET used_at = CASE
                WHEN expires_at < UTC_TIMESTAMP()
                THEN expires_at
                ELSE UTC_TIMESTAMP()
            END
            WHERE user_id = :user_id
            AND used_at IS NULL'
        )->execute([
            'user_id' => $userId,
        ]);

        if ($expired) {
            $statement = $pdo->prepare(
                'INSERT INTO password_reset_tokens
                    (user_id, token_hash, expires_at, created_at)
                VALUES
                    (:user_id, :token_hash,
                    UTC_TIMESTAMP() - INTERVAL 1 MINUTE,
                    UTC_TIMESTAMP() - INTERVAL 2 MINUTE)'
            );

            $statement->execute([
                'user_id' => $userId,
                'token_hash' => $hash,
            ]);
        } else {
            $statement = $pdo->prepare(
                'INSERT INTO password_reset_tokens
                    (user_id, token_hash, expires_at)
                 VALUES
                    (:user_id, :token_hash,
                     UTC_TIMESTAMP() + INTERVAL 30 MINUTE)'
            );

            $statement->execute([
                'user_id' => $userId,
                'token_hash' => $hash,
            ]);
        }

        return $token;
    }

    private function restorePassword(\PDO $pdo, string $email): void
    {
        $hash = password_hash(self::DEV_PASSWORD, PASSWORD_DEFAULT);

        $statement = $pdo->prepare(
            'UPDATE users SET password_hash = :hash WHERE email = :email'
        );
        $statement->execute([
            'hash' => $hash,
            'email' => $email,
        ]);
    }
}
