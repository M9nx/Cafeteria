# ADR 0003: Session, CSRF, and Middleware Policy

## Status

Accepted — Day 2 (P1-LEAD)

## Context

Authentication and admin features require a consistent way to identify signed-in users, prevent cross-site request forgery on state-changing actions, and enforce role/ownership rules before controllers run business logic. Without a shared policy, each feature PR would reimplement session handling and authorization checks inconsistently.

## Decision

### Session hardening

- PHP sessions are wrapped by `SessionManager` using settings from `config/session.php`.
- Cookie flags: `HttpOnly`, `SameSite=Lax` (default), `Secure` when HTTPS/`SESSION_SECURE=true`.
- Session ID is regenerated after successful login (`session_regenerate_id(true)`).
- Session is destroyed on logout; long-lived business data stays in MySQL, not the session store.
- Authenticated identity is stored under `auth.user` as a small array mapped to `AuthenticatedUser`.

### CSRF strategy

- Synchronizer token pattern via `CsrfTokenManager`.
- Token stored in session; forms include hidden field `_csrf_token`.
- Every POST (and future state-changing route) must validate the token before executing the controller action.
- Invalid or missing tokens reject the request (419/403 convention left to auth routes in P1-INTR).

### Middleware order

For protected routes the order is:

1. **GuestMiddleware** — guest-only auth screens (`/login`, `/forgot-password`, `/reset-password`)
2. **AuthMiddleware** — require signed-in user; store safe internal `auth.intended` path for post-login redirect
3. **AdminMiddleware** — require `ADMIN` role; return 403 for authenticated non-admins

Middleware is registered on routes through the existing `Router` callable chain. Middleware returns a `Response` to short-circuit or `null` to continue.

### Authorization policies

- `AdminPolicy` centralizes admin-module checks.
- `OrderPolicy` centralizes order ownership checks (full lifecycle enforcement arrives in P3).
- Policies answer authorization questions; middleware enforces route-level gates; controllers/services call policies for object-level checks.

## Consequences

### Positive

- One session/CSRF contract for all Day 2+ features
- Testable middleware and policy classes
- Clear extension point for P1-INTR auth routes and P1-BEG3 security tests

### Negative

- Controllers must receive dependencies from the bootstrap container rather than reading superglobals
- Session must be started once per request in bootstrap before routing

## References

- Master issue #1 — security checklist
- Issue #13 — P1 appendix
- Issue #14 — P1-LEAD file contracts
- `docs/security/password-reset-architecture.md`
