<?php

declare(strict_types=1);

namespace Cafeteria\Core\Routing;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

final class Router
{
    /** @var list<Route> */
    private array $routes = [];

    /** @var (callable(class-string): object)|null */
    private $controllerFactory = null;

    /** @var (callable(): ?AuthenticatedUser)|null */
    private $currentUserResolver = null;

    /** @param callable(class-string): object $factory */
    public function setControllerFactory(callable $factory): void
    {
        $this->controllerFactory = $factory;
    }

    /** @param callable(): ?AuthenticatedUser $resolver */
    public function setCurrentUserResolver(callable $resolver): void
    {
        $this->currentUserResolver = $resolver;
    }

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

        if (!preg_match($route->regex(), $request->path(), $matches)) {
            return Response::html('Not Found', 404);
        }

        array_shift($matches);

        /** @var array<string, string> $routeParams */
        $routeParams = [];

        foreach ($route->parameterNames() as $index => $name) {
            $routeParams[$name] = $matches[$index] ?? '';
        }

        [$class, $action] = $route->handler();

        if ($this->controllerFactory !== null) {
            $controller = ($this->controllerFactory)($class);
        } else {
            $controller = new $class();
        }

        return $this->invokeAction($controller, $action, $request, $routeParams);
    }

    /**
     * @param array<string, string> $routeParams
     */
    private function invokeAction(
        object $controller,
        string $action,
        Request $request,
        array $routeParams,
    ): Response {
        $method = new ReflectionMethod($controller, $action);
        $arguments = [];

        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                if ($type->getName() === Request::class) {
                    $arguments[] = $request;
                    continue;
                }

                if ($type->getName() === AuthenticatedUser::class) {
                    if ($this->currentUserResolver === null) {
                        throw new RuntimeException('Current user resolver is not configured.');
                    }

                    $user = ($this->currentUserResolver)();

                    if (!$user instanceof AuthenticatedUser) {
                        return Response::html('Unauthorized', 401);
                    }

                    $arguments[] = $user;
                    continue;
                }
            }

            $name = $parameter->getName();

            if (array_key_exists($name, $routeParams)) {
                $value = $routeParams[$name];

                if ($type instanceof ReflectionNamedType && $type->getName() === 'int') {
                    $arguments[] = (int) $value;
                } else {
                    $arguments[] = $value;
                }

                continue;
            }

            throw new RuntimeException(
                "Cannot resolve parameter \${$name} for {$controller}::{$action}()"
            );
        }

        $result = $method->invokeArgs($controller, $arguments);

        if (!$result instanceof Response) {
            throw new RuntimeException('Controller action must return a Response.');
        }

        return $result;
    }
}
