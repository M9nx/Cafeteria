<?php

declare(strict_types=1);

use Cafeteria\Core\Config\Environment;

Environment::load(dirname(__DIR__) . '/.env');

return [
    'driver' => Environment::get('MAIL_DRIVER', 'log'),
    'from' => Environment::get('MAIL_FROM', 'no-reply@cafeteria.local'),
];