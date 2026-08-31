CREATE TABLE order_status_history (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,

    from_status ENUM(
        'PROCESSING',
        'OUT_FOR_DELIVERY',
        'DONE',
        'CANCELLED'
    ) NULL,

    to_status ENUM(
        'PROCESSING',
        'OUT_FOR_DELIVERY',
        'DONE',
        'CANCELLED'
    ) NOT NULL,

    changed_by_user_id BIGINT UNSIGNED NOT NULL,
    changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    CONSTRAINT fk_order_status_history_order_id
        FOREIGN KEY (order_id)
        REFERENCES orders (id)
        ON UPDATE RESTRICT
        ON DELETE CASCADE,

    CONSTRAINT fk_order_status_history_changed_by_user_id
        FOREIGN KEY (changed_by_user_id)
        REFERENCES users (id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT chk_order_status_history_transition
        CHECK (
            (from_status IS NULL AND to_status = 'PROCESSING')
            OR
            (
                from_status = 'PROCESSING'
                AND to_status IN ('OUT_FOR_DELIVERY', 'CANCELLED')
            )
            OR
            (
                from_status = 'OUT_FOR_DELIVERY'
                AND to_status = 'DONE'
            )
        ),

    INDEX idx_order_status_history_timeline (
        order_id,
        changed_at,
        id
    ),

    INDEX idx_order_status_history_actor (
        changed_by_user_id,
        changed_at
    )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;