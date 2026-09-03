<?php

declare(strict_types=1);

use Cafeteria\Core\Config\Environment;

Environment::load(dirname(__DIR__) . '/.env');

return [
    'driver' => Environment::get('MAIL_DRIVER', 'log'),
    'from' => Environment::get('MAIL_FROM', 'no-reply@cafeteria.local'),
    'host' => Environment::get('MAIL_HOST', ''),
    'port' => (int) Environment::get('MAIL_PORT', '587'),
    'username' => Environment::get('MAIL_USERNAME', ''),
    'password' => Environment::get('MAIL_PASSWORD', ''),
    'encryption' => Environment::get('MAIL_ENCRYPTION', 'tls'),
];
