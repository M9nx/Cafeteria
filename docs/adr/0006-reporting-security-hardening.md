# ADR 0006: Reporting Security Hardening

## Status

Accepted - Day 5 (P4-LEAD), pending implementation verification by P4-INTR, P4-BEG2, and P4-BEG3.

## Context

Day 5 reporting adds a security-sensitive administrative surface: checks summary, user drill-down, export, and optional mail transport hardening for password resets. Report output can include order counts, totals, customer identity, notes, dates, and exportable CSV data.

The current implementation has an admin-only report index route, shared report filter validation, and prepared report queries. Drill-down, export, SMTP transport, and report abuse regression tests are not fully merged at the time of this ADR.

The release gate requires the system to avoid authorization-by-UI, unsafe filter parsing, CSV formula injection, secret leakage, and client-side aggregate authority.

## Decision

All report, drill-down, and export behavior must follow these rules before the release gate can pass:

- `GET /admin/checks`, `GET /admin/checks/users/{id}`, and `GET /admin/checks/export` require admin middleware.
- Controllers may assume admin middleware has run, but services and repositories must still preserve safe authorization and filter assumptions.
- User IDs and date filters require server-side validation before queries are executed.
- Drill-down queries must be scoped to the selected user and validated report filters.
- Export must reuse the same validated filters as the visible report.
- Cancelled-order inclusion must be explicit and must default to exclusion for monetary totals.
- Report totals are server-side authoritative values from repository queries, not JavaScript-calculated totals.
- JavaScript may improve presentation only; it is not an authorization, filter, or aggregate authority.
- Optional SMTP delivery must use a mail transport abstraction instead of embedding transport details in password-reset services or controllers.
- Mail credentials and other secrets must exist only in environment/configuration.
- Password reset request responses remain generic whether or not the email exists.
- Logs must not contain reset tokens, passwords, SMTP credentials, or mail provider secrets.

This ADR does not claim that SMTP delivery or CSV export is already implemented. It defines the required security contract for the downstream P4 implementation packages.

## Security Rules

### Authorization

- Report routes are admin-only.
- Guest access redirects to login.
- Authenticated non-admin access returns 403.
- Hidden links, disabled buttons, or missing UI controls are never sufficient authorization.

### Validation

- `user_id` must be a single scalar positive integer string or integer.
- Unknown users fail with a safe validation message.
- Non-numeric, partially numeric, array-shaped, or duplicate user IDs fail validation.
- `from` and `to` dates must be `YYYY-MM-DD`.
- `from` must not be later than `to`.
- If a maximum report range is later introduced, it must be enforced server-side and covered by tests.
- `include_cancelled` must accept only an explicit allowed scalar such as `1`; array-shaped or duplicate toggles must fail or be deterministically rejected.

### SQL

- All request-controlled values must be bound through PDO prepared statements.
- Dynamic SQL fragments are allowed only when selected from fixed server-side allowlists.
- Sort columns, directions, pagination bounds, and filters must not be concatenated from raw request input.
- Date boundaries must be consistent and inclusive for the documented day range.

### Drill-Down

- Drill-down routes must be registered behind admin middleware.
- Malformed route IDs fail before broadening query scope.
- Drill-down must not expose unrelated users' orders.
- Detail links must preserve validated parent filters where relevant.

### Export

- Export routes must be registered behind admin middleware.
- Export filters must be parsed and validated through the same policy as the visible report.
- Download headers must be fixed or generated only from trusted server values.
- CSV cells beginning with `=`, `+`, `-`, or `@` must be neutralized.
- CSV encoding and line endings must be deterministic enough for regression tests.

### Mail And Reset Secrets

- Password reset tokens remain random, expiring, single-use, and hashed at rest.
- Plain reset tokens may appear only in the generated reset link during delivery.
- Mail drivers read configuration from environment-backed config.
- Log mailers must be development-safe and must not leak credentials.
- SMTP credentials must never be committed.

## Consequences

### Positive

- Reporting behavior has a clear release-gate contract before downstream implementation.
- Export and drill-down reuse the same validation model as summary reports.
- CSV and mail risks are handled as explicit security requirements, not incidental feature details.
- Test owners have concrete abuse cases for final regression evidence.

### Negative

- Downstream P4 work cannot close the final gate until drill-down, export, and report security tests exist.
- Duplicate raw query parameter detection may require request-layer support because PHP normalizes `$_GET` before controllers read it.
- Existing report controller code must become stricter about scalar filters.

## Alternatives Considered

- Rely only on admin middleware: rejected because services and repositories still need safe assumptions for future reuse and tests.
- Implement export as a separate query path: rejected because it risks mismatched authorization and filter behavior.
- Trust browser input types for dates and numeric IDs: rejected because attackers can send raw HTTP requests.
- Defer CSV formula handling to spreadsheet users: rejected because export should be safe by default.
- Log SMTP credentials for troubleshooting: rejected because logs must not become a secret store.

## Verification Requirements

- `composer validate --strict`
- `composer audit`
- `composer test`
- `composer test:integration`
- Full PHP syntax validation across `app`, `bootstrap`, `config`, `database`, `public`, `resources`, `routes`, and `tests`.
- Report index authorization tests for guest, normal user, and admin.
- Drill-down authorization tests for guest, normal user, admin, unknown ID, malformed ID, and cross-user tampering.
- Report filter tests for invalid dates, reversed ranges, unknown users, malformed IDs, duplicate parameters, array parameters, SQLi-like strings, and XSS strings.
- Export tests for guest/user/admin access, filter preservation, safe headers, and CSV formula-injection values.
- Password reset tests proving generic responses, hashed tokens, expiry, single use, and no secret/token logging.
- Upload tests proving size, MIME, extension, generated filenames, path traversal rejection, and structural image validation.

## References

- Master issue #1 - security and reporting scope
- Phase issue #50 - P4 reporting and hardening
- Leaf issue #51 - P4-LEAD audit and release gate
- `docs/security/day-5-security-audit.md`
- `docs/test-plan/release-defect-log.md`
