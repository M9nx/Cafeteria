# ADR 0005: Order State Machine and Conditional Status Updates

## Status

Accepted — Day 4 (P3-LEAD)

## Context

Orders move through fulfillment states after placement. Users may cancel only while an order is still being prepared. Admins advance active orders and may cancel processing orders on behalf of operations. Concurrent requests must not apply incompatible transitions.

The schema already stores:

- `orders.status` with values such as `PROCESSING`, `OUT_FOR_DELIVERY`, `DONE`, `CANCELLED`
- `orders.cancelled_at` for terminal cancellation timestamp
- `order_status_history` rows for audit (`from_status`, `to_status`, `changed_by_user_id`, `changed_at`)

P2 delivered placement and repository methods `cancelIfProcessing()` and `transitionIfCurrent()` that update rows only when the persisted status still matches the expected value.

## Decision

### Allowed fulfillment transitions

| From | To |
|------|-----|
| `PROCESSING` | `OUT_FOR_DELIVERY` |
| `OUT_FOR_DELIVERY` | `DONE` |

Cancellation is **not** modeled as a generic transition to avoid bypassing `cancelled_at` handling. It uses `cancelIfProcessing()` which sets `status = CANCELLED` only when the current status is `PROCESSING`.

### Authorization

- **View:** owner or admin (`OrderPolicy::canViewOrder`).
- **Cancel:** owner or admin when status is `PROCESSING` (`OrderPolicy::canCancelOrder`).
- **Transition:** admin only (`OrderPolicy::canTransitionOrder`) and only for pairs allowed by `OrderTransitionMatrix`.

### Conditional updates and race safety

- Cancellation executes `UPDATE ... WHERE id = ? AND status = 'PROCESSING'`.
- Fulfillment transitions execute `UPDATE ... WHERE id = ? AND status = :from`.
- If `rowCount !== 1`, the service returns a safe validation error asking the actor to refresh.
- Every successful change appends one `order_status_history` record.

### HTTP surface (P3-LEAD)

| Method | Route | Actor | Action |
|--------|-------|-------|--------|
| GET | `/orders` | User | Owned history list |
| GET | `/orders/{id}` | Owner/Admin | Order detail |
| POST | `/orders/{id}/cancel` | Owner/Admin | Cancel processing order |
| GET | `/admin/orders/current` | Admin | Active queue |
| POST | `/admin/orders/{id}/status` | Admin | Valid fulfillment transition |

All POST routes require CSRF tokens.

## Consequences

### Positive

- Invalid transitions fail without corrupting history.
- Concurrent double-clicks lose safely with a user-visible message.
- Authorization is centralized in `OrderPolicy` and enforced in `OrderStatusService`.

### Negative

- UI polish for history, queue, and badges remains with P3-BEG1.
- Read/query DTO refinements remain with P3-INTR.

## References

- Master issue #1 — order lifecycle requirements
- Phase issue #37 — P3 appendix
- Issue #38 — P3-LEAD file contracts
- `app/Repositories/Pdo/PdoOrderCommandRepository.php`
