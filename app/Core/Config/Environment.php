<?php

declare(strict_types=1);

namespace Cafeteria\Core\Config;

use RuntimeException;

final class Environment
{
    private static bool $loaded = false;

    public static function load(string $file): void
    {
        if (self::$loaded) {
            return;
        }

        if (!is_file($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            throw new RuntimeException(
                sprintf('Failed to read environment file: %s', $file)
            );
        }

        foreach ($lines as $lineNumber => $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                throw new RuntimeException(
                    sprintf(
                        'Invalid environment entry on line %d.',
                        $lineNumber + 1
                    )
                );
            }

            [$key, $value] = array_map(
                'trim',
                explode('=', $line, 2)
            );

            if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $key)) {
                throw new RuntimeException(
                    "Invalid environment key: {$key}"
                );
            }

            if (strlen($value) >= 2) {
                $first = $value[0];
                $last = $value[strlen($value) - 1];

                if (
                    ($first === '"' && $last === '"')
                    || ($first === "'" && $last === "'")
                ) {
                    $value = substr($value, 1, -1);
                }
            }

            // Real OS, server, or CI variables have priority over .env.
            if (
                getenv($key) !== false
                || array_key_exists($key, $_ENV)
                || array_key_exists($key, $_SERVER)
            ) {
                continue;
            }

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
        }

        // Mark it loaded only after the complete file was parsed.
        self::$loaded = true;
    }

    public static function get(
        string $key,
        ?string $default = null
    ): ?string {
        if (array_key_exists($key, $_ENV)) {
            return (string) $_ENV[$key];
        }

        $value = getenv($key);

        if ($value !== false) {
            return $value;
        }

        if (array_key_exists($key, $_SERVER)) {
            return (string) $_SERVER[$key];
        }

        return $default;
    }

    public static function required(string $key): string
    {
        $value = self::get($key);

        if ($value === null || trim($value) === '') {
            throw new RuntimeException(
                "Required environment variable is missing: {$key}"
            );
        }

        return $value;
    }

    public static function bool(
        string $key,
        bool $default = false
    ): bool {
        $value = self::get($key);

        if ($value === null) {
            return $default;
        }

        return match (strtolower(trim($value))) {
            '1', 'true', 'yes', 'on' => true,
            '0', 'false', 'no', 'off' => false,

            default => throw new RuntimeException(
                "Environment variable {$key} must be boolean."
            ),
        };
    }

    public static function int(
        string $key,
        int $default
    ): int {
        $value = self::get($key);

        if ($value === null) {
            return $default;
        }

        if (!preg_match('/^\d+$/', $value)) {
            throw new RuntimeException(
                "Environment variable {$key} must be an integer."
            );
        }

        return (int) $value;
    }
}