<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Cafeteria\Core\Auth\AuthMiddleware;
use Cafeteria\Core\Auth\CsrfTokenManager;
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
}
