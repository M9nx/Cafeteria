<?php

declare(strict_types=1);

namespace Cafeteria\Core\Routing;

final class Route
{
    /** @param array{0: class-string, 1: string} $handler */
    /** @param list<callable> $middleware */
    public function __construct(
        private readonly string $method,
        private readonly string $pattern,
        private readonly array $handler,
        private readonly array $middleware = [],
    ) {
    }

    public function method(): string
    {
        return $this->method;
    }

    public function pattern(): string
    {
        return $this->pattern;
    }

    /** @return array{0: class-string, 1: string} */
    public function handler(): array
    {
        return $this->handler;
    }

    /** @return list<callable> */
    public function middleware(): array
    {
        return $this->middleware;
    }

    /** @return list<string> */
    public function parameterNames(): array
    {
        preg_match_all('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', $this->pattern, $matches);

        return $matches[1];
    }

    public function regex(): string
    {
        $regex = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([^/]+)', $this->pattern) ?? $this->pattern;

        return '#^' . $regex . '$#';
    }
}
