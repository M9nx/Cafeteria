<?php

declare(strict_types=1);

namespace Cafeteria\Core\Http;

final class Response
{
    /** @param array<string, string> $headers */
    public function __construct(
        private readonly string $content,
        private readonly int $status = 200,
        private readonly array $headers = ['Content-Type' => 'text/html; charset=UTF-8'],
    ) {
    }

    public static function html(string $content, int $status = 200): self
    {
        return new self($content, $status);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function json(array $payload, int $status = 200): self
    {
        return new self(
            (string) json_encode($payload, JSON_THROW_ON_ERROR),
            $status,
            ['Content-Type' => 'application/json; charset=UTF-8'],
        );
    }

    public static function redirect(string $location, int $status = 302): self
    {
        return new self('', $status, [
            'Location' => $location,
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    public function send(): void
    {
        if (headers_sent()) {
            echo $this->content;

            return;
        }

        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $this->content;
    }
}
