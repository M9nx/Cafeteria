<?php

declare(strict_types=1);

namespace Tests\Support;

use Cafeteria\Core\Auth\AuthMiddleware;
use Cafeteria\Core\Auth\CsrfTokenManager;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\Core\Routing\Router;
use Cafeteria\Core\Session\SessionManager;
use PHPUnit\Framework\TestCase;

abstract class HttpTestCase extends TestCase
{
    protected Router $router;
    protected SessionManager $session;

        protected function setUp(): void
            {
            parent::setUp();

    $_GET = [];
    $_POST = [];
    $_FILES = [];
    $_SERVER = [
        'REQUEST_METHOD' => 'GET',
        'REQUEST_URI' => '/',
    ];

    $app = require dirname(__DIR__, 2) . '/bootstrap/app.php';

    $this->router = $app['router'];
    $this->session = $app['session'];
        $this->session->remove('auth.user');
}

    protected function request(
        string $method,
        string $path,
        array $body = [],
    ): Response {
        $_SERVER['REQUEST_METHOD'] = strtoupper($method);
        $_SERVER['REQUEST_URI'] = $path;
        $_POST = $body;

        return $this->router->dispatch(Request::fromGlobals());
    }

    protected function get(string $path): Response
    {
        return $this->request('GET', $path);
    }

    protected function post(string $path, array $body = []): Response
    {
        return $this->request('POST', $path, $body);
    }

    protected function csrfToken(): string
    {
        $token = $this->session->get('_csrf_token');

        self::assertIsString($token);
        self::assertNotSame('', $token);

        return $token;
    }

    protected function loginAsUser(): void
    {
        $this->session->set(
            AuthMiddleware::SESSION_USER_KEY,
            [
                'id' => 2,
                'email' => 'user@example.test',
                'name' => 'Demo User',
                'role' => 'USER',
            ],
        );
    }

    protected function loginAsAdmin(): void
    {
        $this->session->set(
            AuthMiddleware::SESSION_USER_KEY,
            [
                'id' => 1,
                'email' => 'admin@example.test',
                'name' => 'Demo Admin',
                'role' => 'ADMIN',
            ],
        );
    }

    protected function logout(): void
    {
        $this->session->destroy();
    }

    protected function responseContent(Response $response): string
    {
        ob_start();
        $response->send();

        return (string) ob_get_clean();
    }

    protected function responseStatus(Response $response): int
    {
        $reflection = new \ReflectionClass($response);
        $property = $reflection->getProperty('status');

        return $property->getValue($response);
    }

    protected function responseHeader(
        Response $response,
        string $name,
    ): ?string {
        $reflection = new \ReflectionClass($response);
        $property = $reflection->getProperty('headers');
        $headers = $property->getValue($response);

        return $headers[$name] ?? null;
    }
}
