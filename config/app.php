<?php

declare(strict_types=1);

use Cafeteria\Core\Config\Environment;

Environment::load(dirname(__DIR__) . '/.env');

return [
    'name' => Environment::get('APP_NAME', 'Cafeteria'),
    'environment' => Environment::get('APP_ENV', 'production'),
    'debug' => Environment::bool('APP_DEBUG', false),
    'url' => rtrim(
        Environment::get('APP_URL', 'http://localhost') ?? '',
        '/'
    ),
    'timezone' => Environment::get('APP_TIMEZONE', 'Africa/Cairo'),
    'currency' => Environment::get('APP_CURRENCY', 'EGP'),
    'reset_token_ttl_minutes' => Environment::int('RESET_TOKEN_TTL_MINUTES', 60),
];