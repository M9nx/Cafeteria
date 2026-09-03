-- Day 5 report query tuning (P4-INTR).
-- orders(user_id, created_at) and orders(created_at, user_id) already exist in
-- 006_create_orders_table.sql. This migration adds a lookup index for drill-down
-- line-item fetches without changing existing table definitions.

ALTER TABLE order_items
    ADD INDEX idx_order_items_order_id_lookup (order_id);
