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

    // ---- P3-BEG3 (#42) — Order lifecycle fixtures --------------------
    //
    // Deterministic, persisted-order-shaped rows (id/user_id/status/
    // created_at) used to seed the in-memory SQLite database in the
    // lifecycle Feature tests (OrderCancellationTest,
    // OrderStatusTransitionTest, OrderQueueTest, OrderHistoryTest,
    // AdminOnBehalfOrderTest, OrderDateBoundaryTest). Kept separate
    // from the cart-payload keys above, which describe requests to
    // PlaceOrderRequest/PlaceOrderOnBehalfRequest, not stored rows.
    'lifecycle_orders' => [
        // One order per status, for straightforward "does the queue /
        // history / cancellation rule apply to this status" cases.
        'processing_order' => [
            'id' => 501,
            'user_id' => 2,
            'created_by_user_id' => 2,
            'room_id' => 1,
            'status' => 'PROCESSING',
            'total_amount' => '35.00',
            'created_at' => '2026-09-15 10:00:00',
        ],
        'out_for_delivery_order' => [
            'id' => 502,
            'user_id' => 2,
            'created_by_user_id' => 2,
            'room_id' => 1,
            'status' => 'OUT_FOR_DELIVERY',
            'total_amount' => '20.00',
            'created_at' => '2026-09-15 11:00:00',
        ],
        'done_order' => [
            'id' => 503,
            'user_id' => 2,
            'created_by_user_id' => 2,
            'room_id' => 1,
            'status' => 'DONE',
            'total_amount' => '42.50',
            'created_at' => '2026-09-15 12:00:00',
        ],
        'cancelled_order' => [
            'id' => 504,
            'user_id' => 2,
            'created_by_user_id' => 2,
            'room_id' => 1,
            'status' => 'CANCELLED',
            'total_amount' => '15.00',
            'created_at' => '2026-09-15 13:00:00',
            'cancelled_at' => '2026-09-15 13:05:00',
        ],

        // Owned by a different user, for IDOR/ownership-scoping cases
        // (view/cancel/history must never reach across users).
        'other_users_processing_order' => [
            'id' => 505,
            'user_id' => 3,
            'created_by_user_id' => 3,
            'room_id' => 1,
            'status' => 'PROCESSING',
            'total_amount' => '18.00',
            'created_at' => '2026-09-15 14:00:00',
        ],

        // Admin-on-behalf: user_id (customer) deliberately differs
        // from created_by_user_id (admin who placed it).
        'admin_on_behalf_order' => [
            'id' => 506,
            'user_id' => 2,
            'created_by_user_id' => 1,
            'room_id' => 1,
            'status' => 'PROCESSING',
            'total_amount' => '27.00',
            'created_at' => '2026-09-15 15:00:00',
        ],

        // Inclusive date-boundary cases for a filter of
        // from=2026-09-15 to=2026-09-15 (see OrderDateBoundaryTest):
        // the first two must be INCLUDED, the last two must be
        // EXCLUDED.
        'boundary_start_of_day' => [
            'id' => 507,
            'user_id' => 2,
            'created_by_user_id' => 2,
            'room_id' => 1,
            'status' => 'DONE',
            'total_amount' => '12.00',
            'created_at' => '2026-09-15 00:00:00',
        ],
        'boundary_end_of_day' => [
            'id' => 508,
            'user_id' => 2,
            'created_by_user_id' => 2,
            'room_id' => 1,
            'status' => 'DONE',
            'total_amount' => '12.00',
            'created_at' => '2026-09-15 23:59:59',
        ],
        'boundary_one_second_before_range' => [
            'id' => 509,
            'user_id' => 2,
            'created_by_user_id' => 2,
            'room_id' => 1,
            'status' => 'DONE',
            'total_amount' => '12.00',
            'created_at' => '2026-09-14 23:59:59',
        ],
        'boundary_one_second_after_range' => [
            'id' => 510,
            'user_id' => 2,
            'created_by_user_id' => 2,
            'room_id' => 1,
            'status' => 'DONE',
            'total_amount' => '12.00',
            'created_at' => '2026-09-16 00:00:00',
        ],
    ],
];