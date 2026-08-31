<?php

declare(strict_types=1);

namespace Cafeteria\Core\Auth;

use Cafeteria\Core\Session\SessionManager;

final class CsrfTokenManager
{
    public const FIELD_NAME = '_csrf_token';

    private const SESSION_KEY = '_csrf_token';

    public function __construct(
        private readonly SessionManager $session,
    ) {
    }

    public function generate(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->session->set(self::SESSION_KEY, $token);

        return $token;
    }

    public function token(): string
    {
        $existing = $this->session->get(self::SESSION_KEY);

        if (is_string($existing) && $existing !== '') {
            return $existing;
        }

        return $this->generate();
    }

    public function validate(?string $token): bool
    {
        if (!is_string($token) || $token === '') {
            return false;
        }

        $stored = $this->session->get(self::SESSION_KEY);

        if (!is_string($stored) || $stored === '') {
            return false;
        }

        return hash_equals($stored, $token);
    }

    public function rotate(): string
    {
        return $this->generate();
    }
}
