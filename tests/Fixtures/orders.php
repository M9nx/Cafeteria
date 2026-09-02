<?php

declare(strict_types=1);

return [
    'valid_cart' => [
        'items' => [
            ['product_id' => 1, 'quantity' => 2], // Tea ($10.00 x 2 = $20.00)
            ['product_id' => 2, 'quantity' => 1], // Coffee ($15.00 x 1 = $15.00)
        ],
    ],

    'empty_cart' => [
        'items' => [],
    ],

    'invalid_quantity' => [
        'items' => [
            ['product_id' => 1, 'quantity' => 0],
        ],
    ],

    'negative_quantity' => [
        'items' => [
            ['product_id' => 1, 'quantity' => -2],
        ],
    ],

    'unavailable_product' => [
        'items' => [
            ['product_id' => 3, 'quantity' => 1], // Cola (is_available = false)
        ],
    ],

    'tampered_total_cart' => [
        'items' => [
            ['product_id' => 1, 'quantity' => 1],
        ],
        'total' => '0.01', // Client attempts to force a fake total
    ],

    'statuses' => [
        'processing' => 'PROCESSING',
        'out_for_delivery' => 'OUT_FOR_DELIVERY',
        'done' => 'DONE',
        'cancelled' => 'CANCELLED',
    ],
];