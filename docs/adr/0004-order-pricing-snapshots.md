# ADR 0004: Order Pricing Snapshots and Authoritative Server Totals

## Status

Accepted — Day 3 (P2-LEAD)

## Context

Customers place orders from a browser cart that can show a running total for convenience. Browser state and posted form fields are not trustworthy: quantities, product IDs, and especially totals can be tampered with before submission.

The database schema already stores:

- `orders.total_amount` as the order header total
- `order_items.product_name_snapshot`, `unit_price_snapshot`, `quantity`, and `line_total` per line

We need a clear policy for how totals are calculated, when snapshots are captured, and how persistence stays consistent under failure.

## Decision

### Authoritative server-side pricing

- `PlaceOrderRequest` carries **room**, **notes**, and **line items only** (`product_id`, `quantity`).
- Any client-submitted total field is **ignored** if present in the HTTP payload.
- `OrderService::place()` loads current catalog rows through `ProductRepositoryInterface::findAvailableByIds()`.
- Line totals and the order total are recalculated in PHP using the `Money` value object (integer cents internally; two-decimal string output).
- Unavailable or unknown products cause a safe validation failure before any write.

### Historical snapshots

- At placement time the service copies the **current product name and unit price** into `order_items` snapshot columns.
- Snapshots are written in the same database transaction as the order header and line rows.
- Later catalog price or name changes must **not** mutate existing order history.

### Transaction boundary

- Order header insert and all line inserts occur inside **one PDO transaction** managed by `OrderService`.
- Any failure after `beginTransaction()` triggers `rollBack()` so no orphan order or partial item set remains.
- `PdoOrderCommandRepository` performs prepared inserts only; it does not own transaction boundaries.

### DECIMAL rules

- Money amounts use `DECIMAL(10, 2)` in MySQL and string amounts with scale 2 in PHP.
- Float arithmetic is forbidden for money calculations in the order domain.

## Consequences

### Positive

- Tampered client totals cannot affect persisted orders.
- Order history remains stable for reporting and customer receipts.
- Clear split: service owns pricing rules; repository owns SQL persistence.

### Negative

- Cart UI preview totals are presentation-only and may differ until the server responds.
- Product availability must be re-checked at submission time even if the page was rendered earlier.

## References

- Master issue #1 — ordering requirements
- Phase issue #26 — P2 appendix
- Issue #27 — P2-LEAD file contracts
- Migration `006_create_orders_table.sql`, `007_create_order_items_table.sql`
