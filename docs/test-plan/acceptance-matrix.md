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
| AT-ORD-005 | ORD-004 | Given a failure during multi-item order creation, when database write fails, then the entire transaction rolls back. | No partial or orphaned order records exist. | `OrderTransactionTest` |
| AT-ORD-006 | SEC-001 | Given a tampered client price in the payload, when processed, then server overrides client prices with DB authoritative prices. | Client price manipulation is completely neutralized. | `ClientTamperingTest` |

## Verification

All listed automated security tests were executed successfully during the security regression work.

The detailed results are documented in:

- `docs/test-plan/security-regression.md`
- `docs/test-plan/auth-threat-checklist.md`