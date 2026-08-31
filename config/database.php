<?php

declare(strict_types=1);

use Cafeteria\Core\Config\Environment;

Environment::load(dirname(__DIR__) . '/.env');

return [
    'driver' => 'mysql',
    'host' => Environment::get('DB_HOST', '127.0.0.1'),
    'port' => Environment::int('DB_PORT', 3306),
    'database' => Environment::required('DB_NAME'),
    'username' => Environment::required('DB_USER'),
    'password' => Environment::get('DB_PASSWORD', ''),
    'charset' => Environment::get('DB_CHARSET', 'utf8mb4'),
];