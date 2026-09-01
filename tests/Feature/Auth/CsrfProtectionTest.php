<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Cafeteria\Core\Auth\CsrfTokenManager;
use Tests\Support\HttpTestCase;

final class CsrfProtectionTest extends HttpTestCase
{
    public function test_login_rejects_missing_csrf_token(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@example.test',
            'password' => 'DevPassword123!',
        ]);

        self::assertSame(200, $this->responseStatus($response));
        self::assertStringContainsString(
            'Invalid CSRF token.',
            $this->responseContent($response)
        );
    }

    public function test_login_rejects_invalid_csrf_token(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@example.test',
            'password' => 'DevPassword123!',
            CsrfTokenManager::FIELD_NAME => 'invalid-token',
        ]);

        self::assertSame(200, $this->responseStatus($response));
        self::assertStringContainsString(
            'Invalid CSRF token.',
            $this->responseContent($response)
        );
    }

    public function test_logout_rejects_missing_csrf_token(): void
    {
        $this->loginAsUser();

        $response = $this->post('/logout');

        self::assertSame(403, $this->responseStatus($response));
        self::assertStringContainsString(
            'Invalid CSRF token.',
            $this->responseContent($response)
        );
    }

    public function test_logout_rejects_invalid_csrf_token(): void
    {
        $this->loginAsUser();

        $response = $this->post('/logout', [
            CsrfTokenManager::FIELD_NAME => 'invalid-token',
        ]);

        self::assertSame(403, $this->responseStatus($response));
        self::assertStringContainsString(
            'Invalid CSRF token.',
            $this->responseContent($response)
        );
    }

    public function test_forgot_password_rejects_missing_csrf_token(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => 'admin@example.test',
        ]);

        self::assertSame(200, $this->responseStatus($response));
        self::assertStringContainsString(
            'Invalid CSRF token.',
            $this->responseContent($response)
        );
    }

    public function test_forgot_password_rejects_invalid_csrf_token(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => 'admin@example.test',
            CsrfTokenManager::FIELD_NAME => 'invalid-token',
        ]);

        self::assertSame(200, $this->responseStatus($response));
        self::assertStringContainsString(
            'Invalid CSRF token.',
            $this->responseContent($response)
        );
    }
}
