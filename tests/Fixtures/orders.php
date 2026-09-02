<?php

declare(strict_types=1);

return [
    'valid_cart' => [
        'room_id' => 1,
        'items' => [
            ['product_id' => 1, 'quantity' => 2],
            ['product_id' => 2, 'quantity' => 1],
        ],
    ],

    'empty_cart' => [
        'room_id' => 1,
        'items' => [],
    ],

    'invalid_quantity' => [
        'room_id' => 1,
        'items' => [
            ['product_id' => 1, 'quantity' => 0],
        ],
    ],

    'negative_quantity' => [
        'room_id' => 1,
        'items' => [
            ['product_id' => 1, 'quantity' => -2],
        ],
    ],

    'unavailable_product' => [
        'room_id' => 1,
        'items' => [
            ['product_id' => 3, 'quantity' => 1],
        ],
    ],

    'tampered_total_cart' => [
        'room_id' => 1,
        'items' => [
            ['product_id' => 1, 'quantity' => 1],
        ],
        'total' => '0.01',
    ],

    'tampered_price_payload' => [
        'room_id' => 1,
        'items' => [
            [
                'product_id' => 1,
                'quantity' => 1,
                'price' => '0.01',
            ],
        ],
    ],

    'statuses' => [
        'processing' => 'PROCESSING',
        'out_for_delivery' => 'OUT_FOR_DELIVERY',
        'done' => 'DONE',
        'cancelled' => 'CANCELLED',
    ],
];
