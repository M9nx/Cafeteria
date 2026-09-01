<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Tests\Support\HttpTestCase;
use Cafeteria\Core\Http\Response;

final class AuthMiddlewareTest extends HttpTestCase
{
    public function test_guest_is_redirected_to_login_when_accessing_admin_page(): void
    {
        $response = $this->get('/admin/users');

        self::assertSame(302, $this->getResponseStatus($response));
        self::assertSame('/login', $this->getResponseHeader($response, 'Location'));
    }

    public function test_guest_is_redirected_to_login_when_accessing_admin_categories(): void
    {
        $response = $this->get('/admin/categories');

        self::assertSame(302, $this->getResponseStatus($response));
        self::assertSame('/login', $this->getResponseHeader($response, 'Location'));
    }

    private function getResponseStatus(Response $response): int
    {
        $reflection = new \ReflectionClass($response);
        $property = $reflection->getProperty('status');

        return $property->getValue($response);
    }

    private function getResponseHeader(Response $response, string $name): ?string
    {
        $reflection = new \ReflectionClass($response);
        $property = $reflection->getProperty('headers');
        $headers = $property->getValue($response);

        return $headers[$name] ?? null;
    }
}
