<?php

declare(strict_types=1);

return [
    'admin' => [
        'id' => 1,
        'name' => 'Demo Admin',
        'email' => 'admin@example.test',
        'role' => 'ADMIN',
        'is_active' => true,
    ],

    'user' => [
        'id' => 2,
        'name' => 'Demo User',
        'email' => 'user@example.test',
        'role' => 'USER',
        'is_active' => true,
    ],

    'inactive_user' => [
        'id' => 3,
        'name' => 'Inactive User',
        'email' => 'inactive@example.test',
        'role' => 'USER',
        'is_active' => false,
    ],

    'attacker' => [
        'id' => 4,
        'name' => 'Test Attacker',
        'email' => 'attacker@example.test',
        'role' => 'USER',
        'is_active' => true,
    ],
];