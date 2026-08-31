<?php

declare(strict_types=1);

use Cafeteria\Core\Config\Environment;

Environment::load(dirname(__DIR__) . '/.env');

$appUrl = Environment::get('APP_URL', 'http://127.0.0.1:8000') ?? 'http://127.0.0.1:8000';

return [
    'name' => Environment::get('SESSION_NAME', 'cafeteria_session'),
    'lifetime' => Environment::int('SESSION_LIFETIME', 7200),
    'path' => '/',
    'domain' => Environment::get('SESSION_DOMAIN', '') ?? '',
    'secure' => Environment::bool(
        'SESSION_SECURE',
        str_starts_with(strtolower($appUrl), 'https://')
    ),
    'httponly' => Environment::bool('SESSION_HTTPONLY', true),
    'samesite' => Environment::get('SESSION_SAME_SITE', 'Lax') ?? 'Lax',
];
