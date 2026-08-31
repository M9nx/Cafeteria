CREATE TABLE order_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    product_name_snapshot VARCHAR(150) NOT NULL,
    unit_price_snapshot DECIMAL(10, 2) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    line_total DECIMAL(10, 2) NOT NULL,

    PRIMARY KEY (id),

    CONSTRAINT uq_order_items_order_product
        UNIQUE (order_id, product_id),

    CONSTRAINT fk_order_items_order_id
        FOREIGN KEY (order_id)
        REFERENCES orders (id)
        ON UPDATE RESTRICT
        ON DELETE CASCADE,

    CONSTRAINT fk_order_items_product_id
        FOREIGN KEY (product_id)
        REFERENCES products (id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT chk_order_items_product_name
        CHECK (
            CHAR_LENGTH(TRIM(product_name_snapshot)) > 0
            AND BINARY product_name_snapshot =
                BINARY TRIM(product_name_snapshot)
        ),

    CONSTRAINT chk_order_items_quantity_positive
        CHECK (quantity > 0),

    CONSTRAINT chk_order_items_unit_price_positive
        CHECK (unit_price_snapshot > 0),

    CONSTRAINT chk_order_items_line_total_positive
        CHECK (line_total > 0),

    CONSTRAINT chk_order_items_line_total_matches
        CHECK (
            line_total = unit_price_snapshot * quantity
        ),

    INDEX idx_order_items_product_id (product_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;