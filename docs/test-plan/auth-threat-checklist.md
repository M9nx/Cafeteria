# Authentication and Authorization Threat Checklist

This checklist maps the main authentication and authorization security threats to the automated tests implemented for the project.

## 1. Authentication

| Threat / Case | Security Control | Automated Test | Expected Result |
|---|---|---|---|
| Valid credentials | Credential verification | `LoginTest` | User is authenticated and redirected. |
| Invalid credentials | Generic authentication error | `LoginTest` | Login is rejected without exposing account details. |
| Inactive account | Account status check | `InactiveUserLoginTest` | Inactive user cannot authenticate. |
| Logout/session termination | Session destruction | `LogoutTest` | User is logged out and protected routes require authentication. |
| Password reset with valid token | Hashed, expiring reset token | `PasswordResetTest` | Password is changed and user is redirected to login. |
| Invalid reset token | Token validation | `PasswordResetTest` | Reset request is rejected safely. |
| Expired reset token | Token expiry validation | `PasswordResetTest` | Expired token is rejected. |
| Used reset token | One-time token validation | `PasswordResetTest` | Previously used token cannot be reused. |

## 2. CSRF Protection

| Threat / Case | Security Control | Automated Test | Expected Result |
|---|---|---|---|
| Missing CSRF token | CSRF validation | `CsrfProtectionTest` | State-changing request is rejected. |
| Invalid CSRF token | CSRF validation | `CsrfProtectionTest` | State-changing request is rejected. |
| Valid CSRF token | CSRF validation | `LoginTest`, `LogoutTest` | Legitimate state-changing request is accepted. |

## 3. Authorization

| Threat / Case | Security Control | Automated Test | Expected Result |
|---|---|---|---|
| Regular user accessing admin functionality | Admin middleware | `AdminAuthorizationTest` | Access is denied with HTTP 403. |
| User viewing another user's order | Order ownership policy | `OrderOwnershipPolicyTest` | Access is denied. |
| User cancelling another user's order | Order ownership policy | `OrderOwnershipPolicyTest` | Cancellation is denied. |
| Admin accessing another user's order | Admin authorization | `OrderOwnershipPolicyTest` | Authorized admin access is allowed. |

## 4. Session and Access Control

| Threat / Case | Security Control | Automated Test | Expected Result |
|---|---|---|---|
| Unauthenticated access to protected routes | Authentication middleware | `AdminAuthorizationTest`, `LogoutTest` | Guest is redirected to login. |
| Regular user accessing admin routes | Role-based authorization | `AdminAuthorizationTest` | Request is rejected. |
| Access to another user's resource | Resource ownership validation | `OrderOwnershipPolicyTest` | Unauthorized access is rejected. |

## 5. Password Reset Security

Reset tokens must not be stored in plaintext.

The application stores a SHA-256 hash of the reset token and validates the hash when processing the reset request.

Reset tokens are:

- Unique.
- Time-limited.
- Single-use.
- Invalidated after use.
- Invalidated for the user when a new reset token is created.

Automated coverage is provided by `PasswordResetTest`.

## 6. Manual Security Checks

The following cases should also be verified manually when relevant:

- Attempt to access admin URLs while logged out.
- Attempt to access admin URLs as a regular user.
- Attempt to submit state-changing forms without a CSRF token.
- Attempt to reuse an expired or already-used password reset token.
- Attempt to access another user's order by changing the order ID.
- Verify that authentication errors do not reveal whether an email address exists.
- Verify that sensitive tokens and passwords are not written to application logs.

## 7. Evidence

Security verification evidence should include:

- PHPUnit output for the relevant test classes.
- Relevant migration or implementation evidence.
- Manual verification results where automated coverage is not available.
- Pull Request or issue references for completed security work.

Evidence must not contain passwords, reset tokens, session identifiers, or other sensitive information.
