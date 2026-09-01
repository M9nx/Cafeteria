<?php

declare(strict_types=1);

namespace Tests\Support;

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

    protected function responseContent(Response $response): string
    {
        ob_start();
        $response->send();

        return (string) ob_get_clean();
    }
}
