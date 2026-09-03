# Day 5 Security Audit

## Purpose

This document records the P4-LEAD security audit, full-system regression triage, integration review, and release-gate evidence for issue #51 under phase issue #50.

This is a documentation-only package. Application-code fixes are intentionally assigned to the owning WBS packages instead of being implemented here.

## Audit Date

2026-09-03, Africa/Cairo local time.

## Audited Commit

`679763765603b0d8efbe74d4d538703f5274d93d`

The branch was created from `main` after `git pull --ff-only` fast-forwarded local `main` to `origin/main`.

## Environment And Tool Versions

| Tool | Version / evidence |
|---|---|
| PHP | `PHP 8.5.10 (cli)` |
| Composer | `Composer version 2.10.3 2026-08-27 13:34:23` |
| PHPUnit | `PHPUnit 12.5.34` |
| Database used by tests | `cafeteria_test` from `phpunit.xml` |
| Branch | `fix/51-security-regression-gate` |

## Scope

Reviewed areas:

- Routing and middleware for auth, admin, order, and report paths.
- Controllers, services, policies, validators, repositories, DTOs, and views.
- Session handling, CSRF token handling, password reset, uploads, report queries, and export readiness.
- Automated unit, feature, integration, and Composer quality scripts.
- Existing documentation claims in `docs/test-plan/admin-abuse-checklist.md` and `docs/test-plan/security-regression.md`.

GitHub issue references were opened read-only only:

- https://github.com/M9nx/Cafeteria/issues/50
- https://github.com/M9nx/Cafeteria/issues/51

No GitHub issue, PR, label, assignee, milestone, or branch state was changed.

## Dependency Review

Required dependencies were present on updated `main` or represented by the merged history:

| Dependency | Evidence | Result |
|---|---|---|
| `P3-LEAD` | `docs/adr/0005-order-state-machine.md`; lifecycle routes and services in `routes/orders.php`, `routes/admin.php`, `OrderStatusService`, and `OrderPolicy` | Present |
| `P3-INTR` | Merge #47 / `feat/39-history-queue-report-base`; `ReportQueryService`, `ChecksFilterValidator`, `PdoReportRepository`, `UserOrderQueryService` | Present |
| `P3-BEG3` | Merge #48 / `test/42-order-lifecycle-tests`; issue #42 feature tests and `LifecycleOrdersFixture` | Present |

## Methodology

- Inspected route registration before controller/service review.
- Followed state-changing requests from route to middleware, controller, CSRF validation, service, policy, repository, and view.
- Searched SQL call sites for `query`, `exec`, `prepare`, dynamic `WHERE`, `ORDER BY`, `LIMIT`, and `OFFSET`.
- Reviewed views and JavaScript for contextual escaping and unsafe DOM writes.
- Exercised harmless report-filter XSS and malformed-input payloads through the in-process router.
- Used transaction rollback for the self-deactivation probe so the test database remained unchanged.
- Distinguished implemented controls from downstream P4 routes that are not yet available.

## Commands Executed

| Command | Exit | Relevant output |
|---|---:|---|
| `git status --short` | 0 | Untracked unrelated files: `docs/day-1-foundation-guide.md`, `docs/day-2-authentication-admin-guide.md`, `docs/day-3-catalog-ordering-guide.md` |
| `git branch --show-current` | 0 | Started on `test/42-order-lifecycle-tests` |
| `git log -10 --oneline` | 0 | Started at `62c2e74 docs(test-plan): record HTTP lifecycle coverage in evidence maps` |
| `git rev-parse HEAD` | 0 | Initial observed SHA: `62c2e745ed0434eca5bfdc8da2c6448305217e9c` |
| `php --version` | 0 | `PHP 8.5.10 (cli)` |
| `composer --version` | 0 | `Composer version 2.10.3 2026-08-27 13:34:23` |
| `git switch main` | 0 | Switched to `main`; branch was behind `origin/main` |
| `git pull --ff-only` | 0 | Fast-forwarded `899bed4..6797637` |
| `git switch -c fix/51-security-regression-gate` | 0 | Created local issue branch |
| `git rev-parse HEAD` | 0 | Audited SHA: `679763765603b0d8efbe74d4d538703f5274d93d` |
| `composer validate --strict` | 0 | `./composer.json is valid` |
| `composer audit` | 0 | `No security vulnerability advisories found.` |
| `composer test` | 0 | `Tests: 133, Assertions: 300, Warnings: 1` |
| `./vendor/bin/phpunit --display-warnings` | 0 | `24 tests triggered 1 PHP warning`; `bootstrap/app.php:58` unused `DateTimeZone` import |
| `git diff --check` | 0 | No whitespace errors |
| `find app bootstrap config database public resources routes tests -type f -name "*.php" -print0 2>/dev/null | xargs -0 -n1 php -l` | 0 | No syntax errors; warning at `bootstrap/app.php:58` |
| `composer check` | 0 | Composer validate/lint/test completed; repeated `bootstrap/app.php:58` warning and PHPUnit warning |
| `composer test:unit` | 0 | `OK (43 tests, 66 assertions)` |
| `composer test:integration` | 2 | `Class "Tests\Unit\Services\FakeProductRepository" not found` in `tests/Integration/Orders/OrderTransactionTest.php:55` |
| `composer test:feature` | 0 | `Tests: 88, Assertions: 228, Warnings: 1` |
| In-process dispatch: `/admin/checks` as guest/user/admin with XSS filter | 0 | Guest: `302 /login`; user: `403`; admin XSS payload: raw script absent, escaped script present |
| In-process dispatch: malformed report filters | 0 | Unknown user rejected; malformed `user_id` accepted/ignored/coerced; array `include_cancelled` caused PHP warning |
| In-process dispatch: product create with `_token` | 0 | `RuntimeException: Invalid CSRF token.` |
| Transaction rollback probe: `POST /admin/users/1/deactivate` | 0 | `before=1`, `during=0`, `after=1`; self-deactivation is allowed and rollback restored state |

## Post-Documentation Verification

After creating/updating only the approved documentation files:

| Command | Exit | Relevant output |
|---|---:|---|
| `composer validate --strict` | 0 | `./composer.json is valid` |
| `composer audit` | 0 | `No security vulnerability advisories found.` |
| `composer test` | 0 | `Tests: 133, Assertions: 300, Warnings: 1` |
| `git diff --check` | 0 | No whitespace errors |

No application PHP files changed during this package, so the baseline PHP syntax validation remained the current syntax evidence.

## Control Results

| Control | Result | Evidence |
|---|---|---|
| SQL injection | Passed with exceptions noted | PDO prepared statements are used for request values. Report SQL uses fixed filter fragments with bound values. Malformed report filter coercion is tracked in `SEC-P4-004`. |
| XSS | Passed for reviewed implemented surfaces | Views use `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`; report XSS payload rendered escaped; JS uses `textContent` and DOM creation instead of unsafe HTML insertion. |
| CSRF | Blocked | Server-side token checks exist for state-changing routes, but product admin views render `_token` while the controller expects `_csrf_token`; see `SEC-P4-007`. |
| IDOR / authorization | Blocked | Existing order ownership tests pass and `/admin/checks` is admin-only. Required report drill-down/export routes are unavailable for authorization verification; see `SEC-P4-002` and `SEC-P4-003`. |
| Session security | Blocked | Login regenerates session ID; logout destroys session; cookie flags are configurable. Existing authenticated sessions are not revalidated against current active status; see `SEC-P4-005`. |
| Upload security | Blocked | Size, server MIME inspection, server filenames, and non-public storage are present. Structural image validation is not wired into `SafeUploader`; see `SEC-P4-006`. |
| Password reset and secrets | Passed with unavailable mailer implementation | Tokens are random, hashed, expiring, single-use, and responses are generic. SMTP mailer abstraction is not yet merged and is not claimed as implemented. |
| Reporting and CSV export | Blocked | Report index is admin-only with validated dates. Drill-down and export are unavailable; CSV formula-injection defense cannot be verified. |
| Regression test gate | Blocked | Full suite exits 0 with warning. Isolated integration suite exits 2. |

## Findings

### SEC-P4-001

| Field | Value |
|---|---|
| Finding ID | `SEC-P4-001` |
| Status | Confirmed |
| Category | Regression test |
| Severity | High |
| Location | `tests/Integration/Orders/OrderTransactionTest.php:19`, `tests/Integration/Orders/OrderTransactionTest.php:55`, `tests/Unit/Services/OrderServiceTest.php:227` |
| Evidence | `composer test:integration` exits 2 with `Class "Tests\Unit\Services\FakeProductRepository" not found`. The full suite passes only because the unit suite loads the fake class first. |
| Risk | Required integration regression evidence is order-dependent. A reviewer or CI job running the Integration suite alone receives a hard error and cannot trust the release gate. |
| Owner | `P3-BEG3` |
| Required remediation | Move shared fake repositories to `tests/Support` or define an integration-local fake class, then update imports. |
| Verification | `composer test:integration`; `composer test`; both must complete without errors. |
| Release impact | Blocks release: Yes |

### SEC-P4-002

| Field | Value |
|---|---|
| Finding ID | `SEC-P4-002` |
| Status | Unverified |
| Category | IDOR / report drill-down |
| Severity | High |
| Location | `routes/admin.php:159-169`; `app/Controllers/Admin/ReportController.php:23-63` |
| Evidence | `/admin/checks/users/2` returned `404 Not Found` for an admin dispatch. No drill-down route/action is registered yet. |
| Risk | The required drill-down authorization and ownership-boundary controls cannot be verified. Later implementation could expose cross-user report data without this gate being rerun. |
| Owner | `P4-INTR` |
| Required remediation | Implement the admin-only drill-down route/action using shared filter validation and repository ownership boundaries. |
| Verification | Add and pass report drill-down HTTP tests for guest, normal user, admin, unknown ID, malformed ID, and filter preservation. |
| Release impact | Blocks release: Yes |

### SEC-P4-003

| Field | Value |
|---|---|
| Finding ID | `SEC-P4-003` |
| Status | Unverified |
| Category | Export / CSV |
| Severity | High |
| Location | `routes/admin.php:159-169`; no `ReportExportService` implementation present |
| Evidence | `/admin/checks/export` returned `404 Not Found` for guest, normal user, and admin dispatches. No export route/service is registered. |
| Risk | Export authorization, filter reuse, download headers, and CSV formula-injection defense cannot be verified before release. |
| Owner | `P4-BEG2` |
| Required remediation | Implement the admin-only export route/service with shared validated filters, safe headers, and formula-injection neutralization. |
| Verification | Add and pass export tests for guest/user/admin access, filter tampering, and values beginning with `=`, `+`, `-`, or `@`. |
| Release impact | Blocks release: Yes |

### SEC-P4-004

| Field | Value |
|---|---|
| Finding ID | `SEC-P4-004` |
| Status | Confirmed |
| Category | Report validation |
| Severity | Medium |
| Location | `app/Controllers/Admin/ReportController.php:27-30`, `app/Controllers/Admin/ReportController.php:65-80` |
| Evidence | `user_id=abc`, `user_id=1abc`, and `user_id=999999&user_id=abc` returned status 200 without the expected invalid-user error. `include_cancelled[]=1` returned status 200 and triggered `Array to string conversion` at line 30. |
| Risk | Admin report filters can be silently broadened, narrowed, or warning-generating instead of rejected. This weakens auditability and filter integrity for report/export reuse. |
| Owner | `P4-BEG2` |
| Required remediation | Reject array-valued, duplicate, non-numeric, and partially numeric filter values; accept `include_cancelled` only as an explicit allowed scalar. |
| Verification | Add report filter security tests for malformed IDs, duplicate parameters, array parameters, reversed dates, invalid dates, and SQLi-like strings. |
| Release impact | Blocks release: Yes |

### SEC-P4-005

| Field | Value |
|---|---|
| Finding ID | `SEC-P4-005` |
| Status | Confirmed |
| Category | Session / inactive account authorization |
| Severity | Medium |
| Location | `app/Core/Auth/AuthMiddleware.php:24-28`; `app/Core/Auth/AdminMiddleware.php:20-32`; `app/Services/AuthService.php:26-40` |
| Evidence | `AuthService::login()` uses `findActiveByEmail()` for new sessions, but middleware trusts the existing `auth.user` session array without rechecking current `users.is_active`. Existing tests cover inactive login establishment only. |
| Risk | A deactivated account can retain authorization until its existing session expires or is destroyed. This weakens incident response after account disablement. |
| Owner | `P1-INTR` |
| Required remediation | Revalidate active user status on protected requests or introduce a session-version/revocation mechanism. |
| Verification | Add an HTTP test where a logged-in user is deactivated in the database and then denied access on the next authenticated/admin request. |
| Release impact | Blocks release: Yes |

### SEC-P4-006

| Field | Value |
|---|---|
| Finding ID | `SEC-P4-006` |
| Status | Confirmed |
| Category | Upload |
| Severity | Medium |
| Location | `app/Core/Upload/SafeUploader.php:50-54`; `app/Core/Upload/ImageContentValidator.php:18-32`; `docs/test-plan/admin-abuse-checklist.md` upload hardening notes |
| Evidence | `SafeUploader` uses `finfo(FILEINFO_MIME_TYPE)` but does not call `ImageContentValidator::matchesDeclaredMime()`. The checklist claims `getimagesize` content matching is active. |
| Risk | Upload acceptance depends on server MIME detection only, leaving structural image validation unverified on the actual storage path. Spoofed or polyglot image-like content may be harder to reject consistently. |
| Owner | `P2-BEG2` |
| Required remediation | Wire `ImageContentValidator` into `SafeUploader` and add end-to-end uploader tests for non-image bytes, spoofed MIME, SVG/script content, and double extensions. |
| Verification | Run upload unit/feature tests and the full `composer test` suite after the fix. |
| Release impact | Blocks release: Yes |

### SEC-P4-007

| Field | Value |
|---|---|
| Finding ID | `SEC-P4-007` |
| Status | Confirmed |
| Category | CSRF / admin product workflow |
| Severity | Medium |
| Location | `resources/views/admin/products/form.php:58-62`; `resources/views/admin/products/index.php:116-120`; `app/Controllers/Admin/ProductController.php:207-217` |
| Evidence | Product views render `name="_token"` but `ProductController::verifyCsrf()` reads `CsrfTokenManager::FIELD_NAME` (`_csrf_token`). A no-write dispatch to `POST /admin/products` with `_token` raised `RuntimeException: Invalid CSRF token.` |
| Risk | The control fails closed, so this is not a CSRF bypass, but legitimate admin product create/update/deactivate submissions from rendered forms are blocked and can cause uncaught errors. |
| Owner | `P2-BEG2` |
| Required remediation | Change product forms to use `_csrf_token` or the shared `$csrfField`, and ensure invalid CSRF responses are handled consistently. |
| Verification | Add product create/update/deactivate HTTP tests proving rendered tokens submit successfully and missing/invalid tokens fail safely. |
| Release impact | Blocks release: Yes |

### SEC-P4-008

| Field | Value |
|---|---|
| Finding ID | `SEC-P4-008` |
| Status | Confirmed |
| Category | Admin abuse / account lifecycle |
| Severity | Medium |
| Location | `app/Services/UserService.php:157-169`; `app/Repositories/Pdo/PdoAdminUserRepository.php:243-260` |
| Evidence | Transaction rollback probe for `POST /admin/users/1/deactivate` changed `users.is_active` from `1` to `0` before rollback, while the existing checklist claims self-deactivation is blocked. |
| Risk | An admin can deactivate their own admin account, potentially causing administrative lockout, especially if the account is the only active admin. |
| Owner | `P2-BEG2` |
| Required remediation | Reject self-deactivation and consider a last-active-admin guard. |
| Verification | Add admin user feature tests for self-deactivation and last-active-admin deactivation attempts. |
| Release impact | Blocks release: Yes |

### SEC-P4-009

| Field | Value |
|---|---|
| Finding ID | `SEC-P4-009` |
| Status | Confirmed |
| Category | PHP warning / quality |
| Severity | Low |
| Location | `bootstrap/app.php:58` |
| Evidence | `php -l`, `composer check`, `composer test`, and `./vendor/bin/phpunit --display-warnings` all report `The use statement with non-compound name 'DateTimeZone' has no effect`. |
| Risk | The warning pollutes regression output and makes it harder to see new warnings. It is not a direct vulnerability. |
| Owner | `P4-INTR` |
| Required remediation | Remove the unnecessary import or reference `DateTimeZone` consistently. |
| Verification | `php -l bootstrap/app.php`; `composer test`; both should complete without warnings. |
| Release impact | Blocks release: No |

## Positive Controls That Passed

- Dependency audit found no vulnerable Composer advisories.
- PDO uses exception mode, native prepared statements, default associative fetches, and non-stringified fetches.
- Implemented repository SQL binds user-controlled values; static `WHERE` and `ORDER BY` fragments are not built from request strings.
- Report date validation rejects invalid date formats and reversed ranges.
- Report XSS payload in `from` rendered escaped, not raw.
- Existing order IDOR tests pass for cross-user detail and cancellation.
- `/admin/checks` is protected by admin middleware: guest gets `302 /login`; normal user gets `403 Forbidden`.
- Login regenerates session IDs.
- Logout destroys the authenticated session.
- Password reset tokens are random, hashed at rest, expiring, and marked used.
- Password reset request responses are generic for missing and existing accounts.
- Upload filenames are generated by the server and uploads are stored under `storage/uploads`, outside `public/`.

## Areas Not Verifiable

- `/admin/checks/users/{id}` is not registered yet.
- `/admin/checks/export` is not registered yet.
- CSV formula-injection defense is not implemented yet.
- Download headers for report exports are not implemented yet.
- SMTP mail transport is not implemented yet.
- P4-BEG3 report reconciliation/security/export regression tests are not present yet.
- Duplicate raw query parameter detection cannot be verified because `Request` receives PHP's already-parsed `$_GET`.

## Release-Gate Verdict

`BLOCKED`

Reasons:

- Open High findings: `SEC-P4-001`, `SEC-P4-002`, `SEC-P4-003`.
- Required isolated integration suite fails.
- Required report drill-down and export authorization cannot be verified because routes are unavailable.
- Several Medium findings affect report filter integrity, session revocation, upload validation, product-admin CSRF workflow, and admin self-deactivation.
- Regression output contains a repeatable PHP warning.

## Retest Requirements

Before the release gate can be reconsidered:

- Fix `SEC-P4-001` and rerun `composer test:integration` plus `composer test`.
- Deliver P4-INTR drill-down route/controller/service/query work and rerun drill-down authorization tests.
- Deliver P4-BEG2 export/filter work and rerun export authorization, filter tampering, and CSV formula-injection tests.
- Fix report filter scalar/duplicate validation and rerun malicious filter tests.
- Fix retained inactive-session behavior and rerun authenticated/admin deactivation tests.
- Wire structural upload validation and rerun upload spoofing tests.
- Fix product CSRF field names and rerun product create/update/deactivate HTTP tests.
- Fix admin self-deactivation guard and rerun admin abuse tests.
- Remove PHP warning and rerun `composer check`.
