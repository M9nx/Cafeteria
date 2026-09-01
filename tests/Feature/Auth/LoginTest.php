<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Cafeteria\Core\Auth\AuthMiddleware;
use Cafeteria\Core\Auth\CsrfTokenManager;
use Cafeteria\Core\Http\Response;
use Cafeteria\Core\Database\ConnectionFactory;
use Tests\Support\HttpTestCase;

final class LoginTest extends HttpTestCase
{
    public function test_valid_credentials_login_successfully(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@example.test',
            'password' => 'DevPassword123!',
            CsrfTokenManager::FIELD_NAME => $this->csrfToken(),
        ]);

        self::assertSame(302, $this->responseStatus($response));
        self::assertSame('/', $this->responseHeader($response, 'Location'));

        $user = $this->session->get(AuthMiddleware::SESSION_USER_KEY);

        self::assertIsArray($user);
        self::assertSame('admin@example.test', $user['email']);
        self::assertSame('ADMIN', $user['role']);
    }

    public function test_invalid_credentials_are_rejected_with_generic_message(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@example.test',
            'password' => 'WrongPassword123!',
            CsrfTokenManager::FIELD_NAME => $this->csrfToken(),
        ]);

        self::assertSame(200, $this->responseStatus($response));

        $content = $this->responseContent($response);

        self::assertStringContainsString(
            'Invalid email or password.',
            $content
        );

        self::assertNull(
            $this->session->get(AuthMiddleware::SESSION_USER_KEY)
        );
    }

    public function test_inactive_user_cannot_login(): void
    {
        $pdo = (new ConnectionFactory())->make(
            require dirname(__DIR__, 3) . '/config/database.php'
        );

        $email = 'user@example.test';

        $statement = $pdo->prepare(
            'UPDATE users SET is_active = 0 WHERE email = :email'
        );
        $statement->execute(['email' => $email]);

        try {
            $response = $this->post('/login', [
                'email' => $email,
                'password' => 'DevPassword123!',
                CsrfTokenManager::FIELD_NAME => $this->csrfToken(),
            ]);

            self::assertSame(200, $this->responseStatus($response));

            $content = $this->responseContent($response);

            self::assertStringContainsString(
                'Invalid email or password.',
                $content
            );

            self::assertNull(
                $this->session->get(AuthMiddleware::SESSION_USER_KEY)
            );
        } finally {
            $restore = $pdo->prepare(
                'UPDATE users SET is_active = 1 WHERE email = :email'
            );
            $restore->execute(['email' => $email]);
        }
    }
}
