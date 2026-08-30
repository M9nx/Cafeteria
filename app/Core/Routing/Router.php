<?php

declare(strict_types=1);

namespace Cafeteria\Core\Routing;

use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;

final class Router
{
    /** @var list<Route> */
    private array $routes = [];

    /** @param array{0: class-string, 1: string} $handler */
    /** @param list<callable> $middleware */
    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    /** @param array{0: class-string, 1: string} $handler */
    /** @param list<callable> $middleware */
    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    /** @param array{0: class-string, 1: string} $handler */
    /** @param list<callable> $middleware */
    public function add(string $method, string $path, array $handler, array $middleware = []): void
    {
        $normalized = rtrim($path, '/') ?: '/';
        $this->routes[] = new Route(strtoupper($method), $normalized, $handler, $middleware);
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = $request->path();
        $pathMatches = [];

        foreach ($this->routes as $route) {
            if ($route->pattern() === $path || preg_match($route->regex(), $path)) {
                $pathMatches[] = $route;
            }
        }

        if ($pathMatches === []) {
            return Response::html('Not Found', 404);
        }

        foreach ($pathMatches as $route) {
            if ($route->method() === $method) {
                return $this->runRoute($route, $request);
            }
        }

        return Response::html('Method Not Allowed', 405);
    }

    private function runRoute(Route $route, Request $request): Response
    {
        foreach ($route->middleware() as $middleware) {
            $result = $middleware($request);

            if ($result instanceof Response) {
                return $result;
            }
        }

        [$class, $action] = $route->handler();
        $controller = new $class();

        return $controller->{$action}($request);
    }
}
