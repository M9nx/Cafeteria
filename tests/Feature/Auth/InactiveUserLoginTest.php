<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Cafeteria\Core\Auth\AuthMiddleware;
use Cafeteria\Core\Auth\CsrfTokenManager;
use Cafeteria\Core\Database\ConnectionFactory;
use Tests\Support\HttpTestCase;

final class InactiveUserLoginTest extends HttpTestCase
{
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

        self::assertSame(
            1,
            $statement->rowCount(),
            'Expected seeded demo user before testing inactive login.'
        );

        try {
            $response = $this->post('/login', [
                'email' => $email,
                'password' => 'DevPassword123!',
                CsrfTokenManager::FIELD_NAME => $this->csrfToken(),
            ]);

            self::assertSame(200, $this->responseStatus($response));

            self::assertStringContainsString(
                'Invalid email or password.',
                $this->responseContent($response)
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
