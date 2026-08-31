<?php

declare(strict_types=1);

namespace Cafeteria\Core\Session;

final class FlashBag
{
    private const STORAGE_KEY = '_flash';

    public function __construct(
        private readonly SessionManager $session,
    ) {
    }

    public function flash(string $key, string $message): void
    {
        /** @var array<string, string> $messages */
        $messages = $this->session->get(self::STORAGE_KEY, []);
        $messages[$key] = $message;
        $this->session->set(self::STORAGE_KEY, $messages);
    }

    public function pull(string $key): ?string
    {
        /** @var array<string, string> $messages */
        $messages = $this->session->get(self::STORAGE_KEY, []);

        if (!array_key_exists($key, $messages)) {
            return null;
        }

        $message = $messages[$key];
        unset($messages[$key]);

        if ($messages === []) {
            $this->session->remove(self::STORAGE_KEY);
        } else {
            $this->session->set(self::STORAGE_KEY, $messages);
        }

        return $message;
    }

    /**
     * @return array<string, string>
     */
    public function pullAll(): array
    {
        /** @var array<string, string> $messages */
        $messages = $this->session->get(self::STORAGE_KEY, []);
        $this->session->remove(self::STORAGE_KEY);

        return $messages;
    }
}
