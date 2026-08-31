<?php

declare(strict_types=1);

namespace Cafeteria\Core\Session;

final class SessionManager
{
    private bool $started = false;

    /** @param array<string, mixed> $config */
    public function __construct(
        private readonly array $config,
    ) {
    }

    public function start(): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;

            return;
        }

        if (session_status() === PHP_SESSION_DISABLED) {
            throw new \RuntimeException('Sessions are disabled in this environment.');
        }

        session_name((string) $this->config['name']);
        session_set_cookie_params([
            'lifetime' => (int) $this->config['lifetime'],
            'path' => (string) $this->config['path'],
            'domain' => (string) $this->config['domain'],
            'secure' => (bool) $this->config['secure'],
            'httponly' => (bool) $this->config['httponly'],
            'samesite' => (string) $this->config['samesite'],
        ]);

        session_start();
        $this->started = true;
    }

    public function regenerate(bool $deleteOldSession = true): void
    {
        $this->ensureStarted();
        session_regenerate_id($deleteOldSession);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();

        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        $this->ensureStarted();

        return array_key_exists($key, $_SESSION);
    }

    public function remove(string $key): void
    {
        $this->ensureStarted();
        unset($_SESSION[$key]);
    }

    public function destroy(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool) $params['secure'],
                (bool) $params['httponly']
            );
        }

        session_destroy();
        $this->started = false;
    }

    private function ensureStarted(): void
    {
        if (!$this->started && session_status() !== PHP_SESSION_ACTIVE) {
            $this->start();
        }
    }
}
