<?php

declare(strict_types=1);

return [
    'available' => [
        [
            'id' => 1,
            'name' => 'Tea',
            'price' => '10.00',
            'is_available' => true,
            'deleted_at' => null,
        ],
        [
            'id' => 2,
            'name' => 'Coffee',
            'price' => '15.00',
            'is_available' => true,
            'deleted_at' => null,
        ],
    ],

    'unavailable' => [
        [
            'id' => 3,
            'name' => 'Cola',
            'price' => '20.00',
            'is_available' => false,
            'deleted_at' => null,
        ],
    ],

    'deleted' => [
        [
            'id' => 4,
            'name' => 'Chips',
            'price' => '12.50',
            'is_available' => false,
            'deleted_at' => '2026-01-01 00:00:00',
        ],
    ],
];