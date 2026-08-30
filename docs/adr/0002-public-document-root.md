# ADR 0002: Public Document Root

## Status

Accepted — Day 1 (P0-LEAD)

## Context

PHP applications often expose the entire project tree when the web server document root points at the repository root. That exposes `.env`, source code, logs, and private uploads to direct HTTP access.

## Decision

Configure the web server document root to **`public/` only**.

- `public/index.php` is the sole HTTP entry point.
- Application source (`app/`, `bootstrap/`, `config/`, `routes/`, `resources/`) must not be directly reachable.
- Environment secrets (`.env`), logs (`storage/logs/`), and validated private uploads (`storage/uploads/`) must not be web-accessible.
- Only static assets intentionally placed under `public/assets/` are served directly.

Local development uses:

```bash
php -S 127.0.0.1:8000 -t public
```

## Consequences

### Positive

- Reduces accidental secret and source exposure
- Aligns with common PHP deployment practice
- Clear boundary between public assets and private runtime data

### Negative

- Uploaded files require a controlled download/display path through the application when stored outside `public/`
- Team must verify server configuration on every environment

## References

- Master issue #1 — security baseline
- `SECURITY.md` — upload and secrets rules
