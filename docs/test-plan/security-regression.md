# Security Regression Test Results

This document records the authentication and authorization security regression tests implemented for the project.

## Test Environment

- Branch: `test/18-auth-security-tests`
- Test framework: PHPUnit 12
- PHP: 8.4
- Test scope: Authentication, CSRF protection, authorization, password reset, and order ownership.

## Automated Regression Results

| Test Case | Requirement | Test Class | Request / Check | Expected Result | Result |
|---|---|---|---|---|---|
| Login with valid credentials | AUTH-002 | `LoginTest` | POST `/login` with valid credentials and CSRF token | User is authenticated and redirected to `/` | PASS |
| Login with invalid credentials | AUTH-002 | `LoginTest` | POST `/login` with invalid password | Login is rejected with generic error | PASS |
| Inactive user login | AUTH-002 | `InactiveUserLoginTest` | POST `/login` for inactive user | Authentication is rejected | PASS |
| Logout security | AUTH-002 | `LogoutTest` | POST `/logout` with CSRF token | Session is destroyed and protected access is denied | PASS |
| Missing CSRF token | ENG-001 | `CsrfProtectionTest` | State-changing request without token | Request is rejected | PASS |
| Invalid CSRF token | ENG-001 | `CsrfProtectionTest` | State-changing request with invalid token | Request is rejected | PASS |
| Password reset with valid token | AUTH-003 | `PasswordResetTest` | POST `/reset-password` with valid token | Password changes and user is redirected to login | PASS |
| Invalid password reset token | AUTH-003 | `PasswordResetTest` | POST `/reset-password` with invalid token | Reset is rejected | PASS |
| Expired password reset token | AUTH-003 | `PasswordResetTest` | POST `/reset-password` with expired token | Reset is rejected | PASS |
| Used password reset token | AUTH-003 | `PasswordResetTest` | Reuse a consumed reset token | Reset is rejected | PASS |
| Regular user accessing admin routes | AUTHZ-001 | `AdminAuthorizationTest` | Access `/admin/*` as USER | Access is denied with 403 | PASS |
| Admin accessing admin routes | AUTHZ-001 | `AdminAuthorizationTest` | Access `/admin/*` as ADMIN | Access is allowed | PASS |
| User viewing another user's order | AUTHZ-002 | `OrderOwnershipPolicyTest` | Check another user's order ID | Access is denied | PASS |
| User cancelling another user's order | AUTHZ-002 | `OrderOwnershipPolicyTest` | Attempt cancellation of another user's order | Cancellation is denied | PASS |
| Admin viewing another user's order | AUTHZ-003 | `OrderOwnershipPolicyTest` | Check another user's order as ADMIN | Access is allowed | PASS |
| User cancelling non-processing order | HIST-004 | `OrderOwnershipPolicyTest` | Attempt cancellation when status is not `PROCESSING` | Cancellation is denied | PASS |

## Test Commands

The following targeted PHPUnit test classes were executed successfully during this security work:

```text
tests/Feature/Auth/LoginTest.php
tests/Feature/Auth/LogoutTest.php
tests/Feature/Auth/CsrfProtectionTest.php
tests/Feature/Auth/PasswordResetTest.php
tests/Feature/Auth/AdminAuthorizationTest.php
tests/Feature/Auth/OrderOwnershipPolicyTest.php
tests/Feature/Auth/InactiveUserLoginTest.php