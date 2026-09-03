# Security Regression Test Results
This document records the authentication and authorization security regression tests implemented for the project.
## Test Environment
- Branch: `test/18-auth-security-tests`
- Test framework: PHPUnit 12
- PHP: 8.4
- Database: `cafeteria_test` (migrate + seed before Feature tests)
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
| Cross-user order cancellation (IDOR) | AUTHZ-002 | `OrderCancellationTest` | Attempt to cancel another user's `PROCESSING` order via `OrderStatusService::cancel()` | Service reports "Order not found." (existence not leaked); no repository write occurs | PASS |
| Cancel non-`PROCESSING` order | HIST-004 | `OrderCancellationTest` | Attempt to cancel a `DONE` order owned by the actor | Cancellation is denied by policy before any repository write | PASS |
| Cancellation conditional-update race | LIFE-002 | `OrderCancellationTest` | Conditional `cancelIfProcessing()` update reports lost race despite passing policy check | `InvalidArgumentException` raised; order left unmodified | PASS |
| Invalid order status transition | LIFE-001 | `OrderStatusTransitionTest` | Attempt `PROCESSING -> DONE`, backwards transitions, `DONE -> DONE`, and `-> CANCELLED` via the transition endpoint | Transition rejected; order status is left completely unchanged | PASS |
| Non-admin status transition attempt | AUTHZ-001 | `OrderStatusTransitionTest` | Non-admin actor calls `OrderStatusService::transition()` | Rejected as `Forbidden.` before the order is even read | PASS |
| Transition conditional-update race | LIFE-002 | `OrderStatusTransitionTest` | Conditional `transitionIfCurrent()` update reports lost race despite a matrix-valid move | `InvalidArgumentException` raised; order left unmodified | PASS |
| Cross-user order history access (IDOR) | AUTHZ-002 | `OrderHistoryTest` | Regular user requests another user's order history via `UserOrderQueryService::getUserWithOrders()` | Rejected as `Forbidden.`; repository is never queried | PASS |
| Invalid/malicious date-filter input | HIST-001 | `OrderHistoryTest` | Submit malformed date string, `from` after `to`, or out-of-bounds page number | Request rejected with a field-specific validation message before any query runs | PASS |
| Admin-on-behalf identity spoofing check | ADMIN-004 | `AdminOnBehalfOrderTest` | Admin places an order for a selected customer via `OrderService::placeOnBehalf()` | Persisted order records `user_id` = customer and `created_by_user_id` = admin as distinct values | PASS |
| Admin-on-behalf with inactive/nonexistent customer or room | ADMIN-004 | `AdminOnBehalfOrderTest` | Submit an inactive customer ID, a nonexistent customer ID, or an inactive room ID | Request rejected; no order is persisted | PASS |
| Terminal orders surfaced in fulfillment queue | QUEUE-001 | `OrderQueueTest` | Query `listCurrentQueue()` with orders present in every status | `DONE`/`CANCELLED` orders are excluded from both items and total count | PASS |
| Date-range boundary manipulation | HIST-001 | `OrderDateBoundaryTest` | Orders placed exactly at, one second before, and one second after the `from`/`to` boundary | Boundary orders included; orders one second outside the range excluded | PASS |
## Test Commands
Run the full suite after migrating and seeding the test database:
```bash
composer migrate
composer seed
composer test
```
Targeted auth/security classes:
```text
tests/Feature/Auth/LoginTest.php
tests/Feature/Auth/LogoutTest.php
tests/Feature/Auth/CsrfProtectionTest.php
tests/Feature/Auth/PasswordResetTest.php
tests/Feature/Auth/AdminAuthorizationTest.php
tests/Feature/Auth/OrderOwnershipPolicyTest.php
tests/Feature/Auth/InactiveUserLoginTest.php
tests/Unit/Policies/AdminPolicyTest.php
```
Targeted order lifecycle classes (Issue #42):
```text
tests/Feature/Order/OrderCancellationTest.php
tests/Feature/Order/OrderStatusTransitionTest.php
tests/Feature/Order/OrderQueueTest.php
tests/Feature/Order/OrderHistoryTest.php
tests/Feature/Order/AdminOnBehalfOrderTest.php
tests/Feature/Order/OrderDateBoundaryTest.php
```

## Day 5 Security Regression Gate

Audited commit SHA: `679763765603b0d8efbe74d4d538703f5274d93d`

Audit document: `docs/security/day-5-security-audit.md`

Defect log: `docs/test-plan/release-defect-log.md`

### Commands executed

| Command | Exit | Result |
|---|---:|---|
| `composer validate --strict` | 0 | PASS: `./composer.json is valid` |
| `composer audit` | 0 | PASS: no security vulnerability advisories found |
| `composer test` | 0 | BLOCKED: 133 tests / 300 assertions passed, but 1 PHP warning was reported |
| `./vendor/bin/phpunit --display-warnings` | 0 | BLOCKED: 24 tests triggered warning at `bootstrap/app.php:58` |
| `git diff --check` | 0 | PASS |
| `find app bootstrap config database public resources routes tests -type f -name "*.php" -print0 2>/dev/null \| xargs -0 -n1 php -l` | 0 | BLOCKED: no syntax errors, but warning at `bootstrap/app.php:58` |
| `composer check` | 0 | BLOCKED: command completed, but repeated lint/PHPUnit warning |
| `composer test:unit` | 0 | PASS: 43 tests / 66 assertions |
| `composer test:integration` | 2 | FAIL: `Tests\Unit\Services\FakeProductRepository` not found |
| `composer test:feature` | 0 | BLOCKED: 88 tests / 228 assertions passed, but 1 PHP warning was reported |

### Control summary

| Control | Day 5 result | Evidence |
|---|---|---|
| SQLi result | PASS with report-filter defect | Prepared statements and fixed SQL fragments reviewed; malformed `user_id` handling tracked in REL-P4-004 |
| XSS result | PASS | Report filter payload rendered escaped; views use `htmlspecialchars`; JS uses `textContent` |
| CSRF result | BLOCKED | Server-side POST checks exist, but product admin forms use `_token` instead of `_csrf_token`; REL-P4-007 |
| IDOR result | BLOCKED | Existing order IDOR tests pass; report drill-down route is unavailable; REL-P4-002 |
| Session result | BLOCKED | Login/logout controls pass; retained inactive-session behavior is not enforced; REL-P4-005 |
| Upload result | BLOCKED | Size/MIME/server filename checks exist; structural image validator is not wired into storage path; REL-P4-006 |
| Reporting authorization result | BLOCKED | `/admin/checks` blocks guest/user and allows admin; drill-down/export authorization cannot be verified |
| Export result | BLOCKED | `/admin/checks/export` is unavailable; CSV formula defense cannot be tested; REL-P4-003 |

### Gate counts

| Metric | Count |
|---|---:|
| Open Critical count | 0 |
| Open High count | 3 |
| Blocked manual abuse rows | 7 |
| Failed automated suite commands | 1 |

### Final gate verdict

Historical P4-LEAD result: `BLOCKED`. See P4-BEG3 retest below for the current report/export evidence.

### Required retests

- `composer validate --strict`
- `composer audit`
- `composer test`
- `composer test:integration`
- `composer check`
- PHP syntax validation command from the audit package
- Report drill-down authorization and malformed-ID tests
- Report export authorization, filter tampering, and CSV formula-injection tests
- Inactive-session revocation test
- Product admin CSRF workflow tests
- Upload spoofing/content-validation tests
- Admin self-deactivation abuse test

## P4-BEG3 report abuse and export retest (issue #55)

Branch: `test/55-report-security-regression` (onto `feat/#54_NUM-report-filter-export-fixes`)

Concepts reviewed before this package: aggregate reconciliation, malicious filter input, XSS/SQLi/IDOR regression, CSV formula-injection defense, and severity/evidence recording.

### Report cases

| Test Case | Requirement | Test Class | Request / Check | Expected Result | Result |
|---|---|---|---|---|---|
| Guest blocked from checks | AUTHZ-001 | `ReportSecurityTest` | GET `/admin/checks` | `302` `/login` | PASS |
| Guest blocked from drill-down | AUTHZ-001 | `ReportSecurityTest` | GET `/admin/checks/users/2` | `302` `/login` | PASS |
| Guest blocked from export | AUTHZ-001 | `ReportExportTest` | GET `/admin/checks/export` | `302` `/login` | PASS |
| User forbidden from checks | AUTHZ-001 | `ReportSecurityTest` | GET `/admin/checks` as USER | `403 Forbidden` | PASS |
| User forbidden from drill-down | AUTHZ-001 | `ReportSecurityTest` | GET `/admin/checks/users/1` as USER | `403 Forbidden` | PASS |
| User forbidden from export | AUTHZ-001 | `ReportExportTest` | GET `/admin/checks/export` as USER | `403 Forbidden` | PASS |
| Invalid from date | RPT-002 | `ReportSecurityTest` | `from=not-a-date` | No 500; validation message | PASS |
| Invalid to date | RPT-002 | `ReportSecurityTest` | `to=2026-99-99` | No 500; validation message | PASS |
| Reversed date range | RPT-002 | `ReportSecurityTest` | `from=2026-03-10&to=2026-03-01` | No 500; validation message | PASS |
| Unknown user id | RPT-002 | `ReportSecurityTest` | `user_id=999999` | No 500; user does not exist | PASS |
| SQLi-like user id | RPT-002 | `ReportSecurityTest` | `user_id=1 OR 1=1` | No 500; `User ID must be valid.` | PASS |
| Cross-user drill-down tamper | AUTHZ-001 | `ReportSecurityTest` | `/admin/checks/users/2?user_id=1` | Drill-down stays on user 2; `Demo Admin` not rendered | PASS |
| XSS in from filter | RPT-002 | `ReportSecurityTest` | `from=<script>alert(1)</script>` | Raw script absent; validation message present | PASS |
| Malformed user id `abc` / `1abc` | RPT-002 | `ReportSecurityTest` | GET `/admin/checks?user_id=abc` and `user_id=1abc` | Values rejected | PASS — `User ID must be valid.`; export link hidden |
| Array-shaped include_cancelled | RPT-002 | `ReportSecurityTest` | `include_cancelled[]=1` | Rejected without warning | PASS — `Include cancelled must be a valid flag.` |
| Admin export CSV | RPT-003 | `ReportExportTest` | GET `/admin/checks/export` with valid filters | 200; `text/csv`; attachment filename | PASS |
| Invalid export filters | RPT-003 | `ReportExportTest` | GET `/admin/checks/export?from=not-a-date` | HTML validation page, not CSV / 500 | PASS |
| CSV formula injection | RPT-003 | `ReportExportTest` | Cells starting `= + - @` | Prefixed with `'` | PASS |
| Filter preservation | RPT-001 | `ReportHttpTest` | Checks index with `from`/`to`/`user_id`/`include_cancelled` | Form values and export query string preserved | PASS |
| Fixture reconciliation | RPT-001 | `ReportReconciliationTest` | Default, date, cancelled, and user filters | Per-user counts/amounts match fixture | PASS |

### Commands executed

| Command | Exit | Result |
|---|---:|---|
| `composer validate --strict` | 0 | PASS |
| `php -l bootstrap/app.php` | 0 | PASS: no syntax errors; unused `DateTimeZone` import is gone |
| `./vendor/bin/phpunit tests/Feature/Admin/ReportReconciliationTest.php tests/Feature/Admin/ReportSecurityTest.php tests/Feature/Admin/ReportHttpTest.php tests/Feature/Admin/ReportExportTest.php` | 0 | PASS: 33 tests / 124 assertions, no warnings |
| `composer test` | 0 | PASS: 169 tests / 438 assertions |
| `composer test:integration` | 2 | FAIL: `Tests\Unit\Services\FakeProductRepository` not found (REL-P4-001) |

Targeted P4-BEG3 classes:

```text
tests/Support/ReportOrdersFixture.php
tests/Feature/Admin/ReportReconciliationTest.php
tests/Feature/Admin/ReportSecurityTest.php
tests/Feature/Admin/ReportExportTest.php
tests/Feature/Admin/ReportHttpTest.php
```

### Control summary after P4-BEG3

| Control | Day 5 result | Evidence |
|---|---|---|
| SQLi result | PASS | Prepared statements hold; malformed and SQLi-like `user_id` values are rejected |
| XSS result | PASS | `<script>alert(1)</script>` is not rendered raw on `/admin/checks` |
| CSRF result | PASS | Product create/edit and deactivate forms post `_csrf_token` |
| IDOR result | PASS | Guest/user blocked from checks/drill-down/export; `/admin/checks/users/2?user_id=1` does not leak admin data |
| Session result | BLOCKED | Unchanged; inactive sessions are not revalidated (REL-P4-005) |
| Upload result | BLOCKED | Unchanged; `ImageContentValidator` still not on the storage path (REL-P4-006) |
| Reporting authorization result | PASS | Guest `302`, user `403`, admin `200` for index, drill-down, and export |
| Export result | PASS | Authorization, headers, formula prefix, and invalid-filter HTML fallback tests pass |

### Gate counts after P4-BEG3

| Metric | Count |
|---|---:|
| Open Critical count | 0 |
| Open High count | 1 |
| Open Medium count | 2 |
| Open Low count | 0 |
| Failed automated suite commands | 1 (`composer test:integration`) |

### Final gate verdict after P4-BEG3

`BLOCKED`

Report reconciliation, drill-down/export authorization, CSV formula-injection, malformed-filter rejection, product CSRF, and self-deactivation evidence now exist. The release gate stays blocked on High defect REL-P4-001 and Medium findings REL-P4-005 and REL-P4-006.
