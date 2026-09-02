<?php

declare(strict_types=1);

return [
    'valid_cart' => [
        [
            'product_id' => 1,
            'quantity' => 2,
        ],
        [
            'product_id' => 2,
            'quantity' => 1,
        ],
    ],

    'empty_cart' => [],

    'invalid_quantity' => [
        [
            'product_id' => 1,
            'quantity' => 0,
        ],
    ],

    'duplicate_product' => [
        [
            'product_id' => 1,
            'quantity' => 1,
        ],
        [
            'product_id' => 1,
            'quantity' => 2,
        ],
    ],

    'statuses' => [
        'processing' => 'PROCESSING',
        'out_for_delivery' => 'OUT_FOR_DELIVERY',
        'done' => 'DONE',
        'cancelled' => 'CANCELLED',
    ],
];