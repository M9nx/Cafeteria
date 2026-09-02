<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Cafeteria\Core\Auth\AuthMiddleware;
use Cafeteria\Core\Auth\CsrfTokenManager;
use Tests\Support\HttpTestCase;

final class LogoutTest extends HttpTestCase
{
    public function test_authenticated_user_can_logout_with_valid_csrf(): void
    {
        $this->loginAsUser();

        $response = $this->post('/logout', [
            CsrfTokenManager::FIELD_NAME => $this->csrfToken(),
        ]);

        self::assertSame(302, $this->responseStatus($response));
        self::assertSame('/login', $this->responseHeader($response, 'Location'));
        self::assertNull(
            $this->session->get(AuthMiddleware::SESSION_USER_KEY)
        );
    }

    public function test_logout_without_csrf_is_rejected(): void
    {
        $this->loginAsUser();

        $response = $this->post('/logout');

        self::assertSame(403, $this->responseStatus($response));
        self::assertStringContainsString(
            'Invalid CSRF token.',
            $this->responseContent($response)
        );

        self::assertIsArray(
            $this->session->get(AuthMiddleware::SESSION_USER_KEY)
        );
    }

    public function test_logout_protects_authenticated_routes_after_session_is_destroyed(): void
    {
        $this->loginAsUser();

        $response = $this->post('/logout', [
            CsrfTokenManager::FIELD_NAME => $this->csrfToken(),
        ]);

        self::assertSame(302, $this->responseStatus($response));

        $protectedResponse = $this->get('/admin/users');

        self::assertSame(302, $this->responseStatus($protectedResponse));
        self::assertSame(
            '/login',
            $this->responseHeader($protectedResponse, 'Location')
        );
    }
}
