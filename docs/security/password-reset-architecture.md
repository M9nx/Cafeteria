# Password Reset Architecture

## Scope

This document defines the reset-token lifecycle for Day 2 (P1). Persistence is implemented in P1-INTR; this package publishes the contract and rules.

## Token generation

- Plain token: `bin2hex(random_bytes(32))` (64 hex characters).
- Stored value: `hash('sha256', $plainToken)` only — never persist or log the plain token.
- Database column: `password_reset_tokens.token_hash` (`CHAR(64)`).

## Lifecycle

| Step | Rule |
|------|------|
| Request reset | Invalidate existing unused tokens for the user (`invalidateForUser`) |
| Create token | Insert row with `expires_at = now + TTL` |
| Deliver link | Development: log or mailer stub using `APP_URL`; production: email only |
| Validate | `findValidByHash()` requires `used_at IS NULL` and `expires_at > now` |
| Complete reset | Mark token used (`markUsed`), update password hash, destroy other sessions for user |
| Response | Generic success message whether or not the email exists |

## Configuration

| Setting | Default | Purpose |
|---------|---------|---------|
| `RESET_TOKEN_TTL_MINUTES` | `60` | Token expiry window (documented in P1-INTR `.env.example`) |
| `APP_URL` | — | Base URL for reset links |

## Throttling notes

- Apply per-email and per-IP rate limits at the service layer (P1-INTR).
- Do not reveal whether an email is registered in error messages or response timing beyond normal variance.

## Repository contract

See `PasswordResetTokenRepositoryInterface`:

- `create(userId, tokenHash, expiresAt)`
- `findValidByHash(tokenHash)`
- `markUsed(tokenId)`
- `invalidateForUser(userId)`

## References

- Migration `003_create_password_reset_tokens_table.sql`
- ADR 0003 — session and CSRF baseline
- Issue #14 — P1-LEAD
