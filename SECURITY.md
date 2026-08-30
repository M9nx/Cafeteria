# Security Policy

## Reporting

If you discover a security vulnerability, report it privately to the team lead (Mounir Sabry) before opening a public issue. Include steps to reproduce, affected routes or files, and suggested severity.

## Baseline rules

These rules apply to every package in the project:

### Secrets and configuration

- Never commit `.env`, real passwords, API keys, or production credentials.
- Use `.env.example` with placeholder values only.
- Keep source, logs, and private uploads outside the web document root (`public/`).

### Authentication and sessions

- Use `password_hash()` and `password_verify()`; never store reversible passwords.
- Regenerate session ID after successful login; destroy session on logout.
- Use secure session cookie settings in production (`HttpOnly`, `Secure`, `SameSite`).

### Input and output

- Never concatenate user input into SQL; use PDO prepared statements only.
- Escape output with `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`.
- Validate all input on the server; client-side checks are UX only.

### CSRF and authorization

- Include CSRF tokens on every state-changing form and verify on POST.
- Enforce role and ownership checks in controllers/policies and queries — not by hiding buttons alone.

### Password reset

- Reset tokens must be random, hashed at rest, expiring, and single-use.
- Never allow unauthenticated direct password overwrite without a valid token.

### File uploads

- Generate stored filenames; validate MIME, extension, and size.
- Reject executable content; store uploads outside `public/` when possible.

### Errors and logging

- Log production errors without exposing stack traces, SQL, or secrets to users.
- Do not commit runtime logs or uploaded user files.

## Sensitive paths

Changes under these paths require lead review (see `.github/CODEOWNERS`):

- `app/Core/`
- `app/Policies/`
- `database/`
- `config/`
- `bootstrap/`
