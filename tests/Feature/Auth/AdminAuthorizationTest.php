<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Tests\Support\HttpTestCase;

final class AdminAuthorizationTest extends HttpTestCase
{
    public function test_guest_is_redirected_to_login_from_admin_route(): void
    {
        $response = $this->get('/admin/categories');

        self::assertSame(302, $this->responseStatus($response));
        self::assertSame('/login', $this->responseHeader($response, 'Location'));
    }

    public function test_regular_user_is_forbidden_from_admin_route(): void
    {
        $this->loginAsUser();

        $response = $this->get('/admin/categories');

        self::assertSame(403, $this->responseStatus($response));
        self::assertStringContainsString(
            'Forbidden',
            $this->responseContent($response)
        );
    }

    public function test_admin_can_access_admin_route(): void
    {
        $this->loginAsAdmin();

        $response = $this->get('/admin/categories');

        self::assertNotSame(401, $this->responseStatus($response));
        self::assertNotSame(403, $this->responseStatus($response));
    }
}