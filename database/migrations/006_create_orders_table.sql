CREATE TABLE orders (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    created_by_user_id BIGINT UNSIGNED NOT NULL,
    room_id BIGINT UNSIGNED NOT NULL,

    status ENUM(
        'PROCESSING',
        'OUT_FOR_DELIVERY',
        'DONE',
        'CANCELLED'
    ) NOT NULL DEFAULT 'PROCESSING',

    notes TEXT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    cancelled_at DATETIME NULL,

    PRIMARY KEY (id),

    CONSTRAINT fk_orders_user_id
        FOREIGN KEY (user_id)
        REFERENCES users (id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT fk_orders_created_by_user_id
        FOREIGN KEY (created_by_user_id)
        REFERENCES users (id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT fk_orders_room_id
        FOREIGN KEY (room_id)
        REFERENCES rooms (id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT chk_orders_total_amount_positive
        CHECK (total_amount > 0),

    CONSTRAINT chk_orders_cancellation_state
        CHECK (
            (
                status = 'CANCELLED'
                AND cancelled_at IS NOT NULL
                AND cancelled_at >= created_at
            )
            OR
            (
                status <> 'CANCELLED'
                AND cancelled_at IS NULL
            )
        ),

    INDEX idx_orders_user_created_at (
        user_id,
        created_at
    ),

    INDEX idx_orders_created_by_user_id (
        created_by_user_id
    ),

    INDEX idx_orders_room_id (
        room_id
    ),

    INDEX idx_orders_status_created_at (
        status,
        created_at
    ),

    INDEX idx_orders_report_created_user (
        created_at,
        user_id
    )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;