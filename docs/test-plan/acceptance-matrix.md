# Acceptance Test Matrix

This document defines acceptance tests for the authentication and authorization security requirements.

## Authentication and Authorization Acceptance Tests

| Test ID | Requirement ID | Scenario | Expected Result | Automated Test |
|---|---|---|---|---|
| AT-AUTH-001 | AUTH-002 | Given a registered user with valid credentials, when the user submits the login form, then authentication succeeds. | User is authenticated and redirected to the authenticated area. | `LoginTest` |
| AT-AUTH-002 | AUTH-002 | Given a registered user with invalid credentials, when the user submits the login form, then authentication is rejected. | User remains unauthenticated and receives a generic error message. | `LoginTest` |
| AT-AUTH-003 | AUTH-002 | Given an inactive user, when the user submits valid credentials, then authentication is rejected. | Inactive user cannot log in. | `InactiveUserLoginTest` |
| AT-AUTH-004 | AUTH-002 | Given an authenticated user, when the user submits logout, then the session is terminated. | User is logged out and protected routes require authentication. | `LogoutTest` |
| AT-AUTH-005 | ENG-001 | Given a state-changing request without a CSRF token, when the request is submitted, then the request is rejected. | Request is rejected according to the project's CSRF convention. | `CsrfProtectionTest` |
| AT-AUTH-006 | ENG-001 | Given a state-changing request with an invalid CSRF token, when the request is submitted, then the request is rejected. | Request is rejected according to the project's CSRF convention. | `CsrfProtectionTest` |
| AT-AUTH-007 | AUTH-003 | Given a user with a valid password reset token, when the user submits a valid new password, then the password is changed. | Password is changed and the user is redirected to login. | `PasswordResetTest` |
| AT-AUTH-008 | AUTH-003 | Given an invalid, expired, or previously used reset token, when the user attempts a password reset, then the request is rejected. | Password is not changed. | `PasswordResetTest` |
| AT-ADMIN-001 | AUTHZ-001 | Given a regular user, when the user accesses an admin-only route, then access is denied. | User receives HTTP 403 and cannot access admin functionality. | `AdminAuthorizationTest` |
| AT-ADMIN-002 | AUTHZ-001 | Given an authenticated admin, when the admin accesses an admin route, then access is allowed. | Admin can access the requested admin functionality. | `AdminAuthorizationTest` |
| AT-ADMIN-003 | AUTHZ-002 | Given a regular user, when the user attempts to view another user's order, then access is denied. | User cannot view another user's order. | `OrderOwnershipPolicyTest` |
| AT-ADMIN-004 | AUTHZ-002 | Given a regular user, when the user attempts to cancel another user's order, then cancellation is denied. | Another user's order cannot be cancelled. | `OrderOwnershipPolicyTest` |
| AT-ADMIN-005 | AUTHZ-003 | Given an admin, when the admin accesses another user's order, then access is allowed. | Admin can access the selected user's order. | `OrderOwnershipPolicyTest` |
| AT-ADMIN-006 | HIST-004 | Given an order that is not in `PROCESSING` status, when cancellation is requested, then cancellation is denied. | Order status remains unchanged. | `OrderOwnershipPolicyTest` |

## Order Processing Acceptance Tests (Issue #31)

| Test ID | Requirement ID | Scenario | Expected Result | Automated Test |
|---|---|---|---|---|
| AT-ORD-001 | ORD-001 | Given a valid shopping cart payload, when an order is placed, then the order is processed successfully. | Order is created with valid status and items. | `PlaceOrderTest` |
| AT-ORD-002 | ORD-001 | Given an empty cart or invalid quantity, when an order is submitted, then the placement is rejected. | Order placement fails gracefully. | `PlaceOrderTest` |
| AT-ORD-003 | ORD-002 | Given items in a cart, when totals are calculated, then the server calculates exact totals using decimal rules and ignores client-submitted total fields. | Server-authoritative calculation matches expected sum. | `OrderTotalTest` |
| AT-ORD-004 | ORD-003 | Given an order with items, when product price or name changes later in catalog, then historical order item snapshots remain unchanged. | Historic price and name snapshot integrity is maintained. | `OrderSnapshotTest` |
| AT-ORD-005 | ORD-004 | Given a failure during multi-item order creation, when database write fails, then the entire transaction rolls back. | No partial or orphaned order records exist. | `OrderTransactionTest`, `Integration/Orders/OrderTransactionTest` |
| AT-ORD-006 | SEC-001 | Given a tampered client price in the payload, when processed, then server overrides client prices with DB authoritative prices. | Client price manipulation is completely neutralized. | `ClientTamperingTest` |

## Order Lifecycle Acceptance Tests (Issue #42)

| Test ID | Requirement ID | Scenario | Expected Result | Automated Test |
|---|---|---|---|---|
| AT-CANCEL-001 | AUTHZ-002 | Given a `PROCESSING` order owned by another user, when a user attempts to cancel it, then cancellation is denied without revealing the order exists. | Service reports "Order not found." rather than "Forbidden."; no repository write occurs. | `OrderCancellationTest` |
| AT-CANCEL-002 | HIST-004 | Given an order not in `PROCESSING` status, when its owner requests cancellation, then cancellation is denied before any repository write. | Order status remains unchanged; `cancelIfProcessing` is never called. | `OrderCancellationTest` |
| AT-CANCEL-003 | LIFE-002 | Given a `PROCESSING` order, when the conditional repository update loses a concurrent race, then the service reports a distinct, catchable failure. | `InvalidArgumentException` is raised; order is left unmodified. | `OrderCancellationTest` |
| AT-TRANS-001 | LIFE-001 | Given an admin actor, when a valid transition (`PROCESSING -> OUT_FOR_DELIVERY -> DONE`) is requested, then the transition is applied. | Order status advances exactly one step per the transition matrix. | `OrderStatusTransitionTest` |
| AT-TRANS-002 | LIFE-001 | Given any actor, when an invalid or out-of-order transition is requested (including reaching `CANCELLED` via this path), then the transition is rejected. | Order status is left completely unchanged; no repository write occurs. | `OrderStatusTransitionTest` |
| AT-TRANS-003 | AUTHZ-001 | Given a non-admin actor, when a status transition is requested, then the request is rejected before the order is read. | `Forbidden.` is raised; repository is never queried. | `OrderStatusTransitionTest` |
| AT-QUEUE-001 | QUEUE-001 | Given orders in every status, when the admin current-order queue is requested, then only `PROCESSING`/`OUT_FOR_DELIVERY` orders are listed, oldest first. | `DONE`/`CANCELLED` orders are excluded from both items and total count. | `OrderQueueTest` |
| AT-HIST-001 | AUTHZ-002 | Given a regular user, when the user requests another user's order history, then the request is rejected before any query runs. | `Forbidden.` is raised; repository is never queried. | `OrderHistoryTest` |
| AT-HIST-002 | HIST-001 | Given an invalid page number, malformed date, or `from` after `to`, when order history is requested, then the request is rejected with a field-specific message. | History query is never executed for an invalid filter. | `OrderHistoryTest` |
| AT-ADMIN-007 | ADMIN-004 | Given an admin placing an order for a selected active customer, when the order is stored, then the customer and the creator are recorded as distinct identities. | `user_id` = selected customer; `created_by_user_id` = admin; the two differ. | `AdminOnBehalfOrderTest` |
| AT-ADMIN-008 | ADMIN-004 | Given an inactive/non-existent customer or inactive room, when an admin attempts to place an order on their behalf, then the request is rejected. | No order is persisted; a field-specific validation message is returned. | `AdminOnBehalfOrderTest` |
| AT-DATE-001 | HIST-001 | Given orders at the exact start (`00:00:00`) and end (`23:59:59`) of a filtered day, when the date range is applied, then both boundary orders are included. | Orders on the `from`/`to` boundary are present in the result. | `OrderDateBoundaryTest` |
| AT-DATE-002 | HIST-001 | Given orders one second before `from` or one second after `to`, when the date range is applied, then those orders are excluded. | Orders just outside the inclusive range do not appear in the result. | `OrderDateBoundaryTest` |

## Checks Report Acceptance Tests (Issue #55)

| Test ID | Requirement ID | Scenario | Expected Result | Automated Test |
|---|---|---|---|---|
| AT-RPT-001 | RPT-001 | Given a deterministic fixture of users and orders, when the default checks summary runs, then per-user `order_count` and `total_amount` match fixture sums with cancelled rows excluded. | User A = 5 orders / 400.00; User B = 1 order / 150.00. | `ReportReconciliationTest` |
| AT-RPT-002 | RPT-001 | Given orders on, before, and after `2026-03-01`, when `from` and `to` are that day, then only in-range rows including both boundaries are counted. | User A = 3 / 300.00; User B = 1 / 150.00. | `ReportReconciliationTest` |
| AT-RPT-003 | RPT-001 | Given a cancelled order inside the filtered day, when `include_cancelled` is true, then that order is added to the user total. | User A = 4 orders / 800.00 for `2026-03-01`. | `ReportReconciliationTest` |
| AT-RPT-004 | RPT-001 | Given a user filter for fixture User A, when the summary runs, then only that user is returned. | One row; User A = 5 / 400.00. | `ReportReconciliationTest` |
| AT-RPT-005 | AUTHZ-001 | Given a guest or regular user, when they request `/admin/checks`, `/admin/checks/users/{id}`, or `/admin/checks/export`, then access is denied. | Guest `302 /login`; user `403 Forbidden`. | `ReportSecurityTest`, `ReportExportTest`, `ReportHttpTest` |
| AT-RPT-006 | RPT-002 | Given an admin, when invalid dates, reversed range, or an unknown user id are submitted, then the page stays safe and shows a validation message. | No HTTP 500; field-specific error text. | `ReportSecurityTest`, `ReportHttpTest` |
| AT-RPT-007 | AUTHZ-001 | Given an admin on another user's drill-down, when `user_id` is tampered to a different user, then the page still scopes to the path user and does not leak the other user's name. | `/admin/checks/users/2?user_id=1` does not render `Demo Admin`. | `ReportSecurityTest` |
| AT-RPT-008 | RPT-003 | Given an admin, when CSV export is requested with valid filters, then the file downloads with CSV headers. | HTTP 200; `text/csv`; `Content-Disposition` attachment. | `ReportExportTest`, `ReportHttpTest` |
| AT-RPT-009 | RPT-003 | Given report cells that start with `=`, `+`, `-`, or `@`, when CSV is built, then those cells are prefixed with `'`. | Formula-leading values are neutralized; ordinary names are unchanged. | `ReportExportTest` |

## Verification

Authentication and authorization tests were executed during the Day 2 security regression work.

Order feature tests in `tests/Feature/Order/` exercise `OrderService` with deterministic fixtures and verify validation, totals, snapshots, tampering resistance, and transaction rollback behavior.

Order lifecycle tests in `tests/Feature/Order/` (Issue #42) exercise `OrderStatusService`, `UserOrderQueryService`, and `PdoOrderQueryRepository` with the `lifecycle_orders` fixtures in `tests/Fixtures/orders.php`, and verify ownership-safe cancellation, valid/invalid status transitions, the admin fulfillment queue, user history scoping and date-filter validation, admin order-on-behalf customer/creator identity, and inclusive date-range boundaries.

Reporting tests in `tests/Feature/Admin/` (Issue #55) reconcile fixture aggregates, cover guest/user/admin authorization for checks/drill-down/export, reject invalid dates and unknown users, and smoke CSV formula-injection defense.

Detailed results are documented in:

- `docs/test-plan/security-regression.md`
- `docs/test-plan/auth-threat-checklist.md`
- `docs/test-plan/release-defect-log.md`