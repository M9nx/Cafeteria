# Release Defect Log

P4-LEAD release triage for issue #51, updated by P4-BEG3 (#55) after report reconciliation and security/export regression.

| Defect ID | Source finding/test | Severity | Description | Owner WBS ID | Branch/PR | Status | Fix evidence | Retest command | Retest result | Release blocker |
|---|---|---|---|---|---|---|---|---|---|---|
| REL-P4-001 | `SEC-P4-001`; `composer test:integration` | High | Integration suite fails in isolation because `Tests\Integration\Orders\OrderTransactionTest` imports `Tests\Unit\Services\FakeProductRepository`, which is not autoloaded when only the Integration suite runs. | `P3-BEG3` | None | BLOCKED | None | `composer test:integration`; `composer test` | P4-BEG3 retest: `composer test:integration` exit 2, class not found; `composer test` exit 0 (168 tests / 425 assertions) | Yes |
| REL-P4-002 | `SEC-P4-002`; `ReportSecurityTest` | High | Required checks drill-down route/action was unavailable, so admin-only drill-down authorization and IDOR behavior could not be verified. | `P4-INTR` | PR #58 / `feat/52-reporting-mailer-performance` | CLOSED | GET `/admin/checks/users/{id}` is registered behind admin middleware | `./vendor/bin/phpunit tests/Feature/Admin/ReportSecurityTest.php tests/Feature/Admin/ReportHttpTest.php` | PASS: guest 302, user 403, admin 200; `/admin/checks/users/2?user_id=1` does not leak `Demo Admin`; unknown user is a safe validation error | No |
| REL-P4-003 | `SEC-P4-003`; `ReportExportTest` | High | Required checks export route/service was unavailable, so export authorization, filter reuse, safe headers, and CSV formula-injection defense could not be verified. | `P4-BEG2` | PR #60 / `feat/#54_NUM-report-filter-export-fixes` | CLOSED | `ReportExportService` plus GET `/admin/checks/export`; invalid filters render the report page instead of CSV; formula cells prefixed with `'`; `fputcsv()` escape argument set | `./vendor/bin/phpunit tests/Feature/Admin/ReportExportTest.php tests/Feature/Admin/ReportHttpTest.php` | PASS: guest 302, user 403, admin 200 CSV; invalid export filters return HTML validation errors; formula prefixes neutralized | No |
| REL-P4-004 | `SEC-P4-004`; `ReportSecurityTest` | Medium | Report controller silently accepts or coerces malformed `user_id` values and warns on array-shaped `include_cancelled`. | `P4-BEG2` | PR #60 / `feat/#54_NUM-report-filter-export-fixes` | CLOSED | `ReportController` rejects non-digit `user_id` and non-scalar `include_cancelled`/`from`/`to` with `InvalidArgumentException` | `./vendor/bin/phpunit tests/Feature/Admin/ReportSecurityTest.php` | PASS: `abc`, `1abc`, `0`, `-4`, and `1 OR 1=1` return `User ID must be valid.`; `include_cancelled[]=1` returns `Include cancelled must be a valid flag.`; export link hidden while errors are present | No |
| REL-P4-005 | `SEC-P4-005`; session code review | Medium | Existing authenticated sessions are not revalidated against `users.is_active`, so deactivated users can retain authorization until session expiry/logout. | `P1-INTR` | None | OPEN | None | Auth HTTP test for deactivated logged-in user followed by protected request | Escalated: no owner fix in P4-BEG2 scope; code path unchanged | Yes |
| REL-P4-006 | `SEC-P4-006`; upload code review | Medium | `ImageContentValidator` exists but is not called by `SafeUploader`, contradicting documented `getimagesize` enforcement on the storage path. | `P2-BEG2` | None | OPEN | None | Upload spoofing tests plus `composer test` | Escalated: no owner fix in P4-BEG2 scope; code path unchanged | Yes |
| REL-P4-007 | `SEC-P4-007`; product CSRF dispatch | Medium | Product admin forms rendered `_token` while CSRF validation expects `_csrf_token`. | `P4-BEG2` | PR #60 / `feat/#54_NUM-report-filter-export-fixes` | CLOSED | Create/edit and deactivate forms post `_csrf_token` | Inspect `resources/views/admin/products/form.php` and `index.php` | PASS: no remaining `_token` fields under `resources/views` | No |
| REL-P4-008 | `SEC-P4-008`; rollback self-deactivation probe | Medium | Admin self-deactivation is allowed despite the existing checklist claiming it is blocked. | `P4-BEG2` | PR #60 / `feat/#54_NUM-report-filter-export-fixes` | CLOSED | `UserService::deactivate()` rejects self-deactivation and the last active admin | Code review of `UserService::deactivate()` plus `countActiveAdmins()` | PASS: own account and last-admin deactivation throw `RuntimeException` before the repository write | No |
| REL-P4-009 | `SEC-P4-009`; PHP lint/PHPUnit warning | Low | `bootstrap/app.php:58` had an unused `DateTimeZone` import that triggered a repeatable PHP warning. | `P4-INTR` | PR #58 / `feat/#54_NUM-report-filter-export-fixes` | CLOSED | Unused import removed; timezone constructed as `\DateTimeZone` | `php -l bootstrap/app.php` | PASS: no syntax errors and no unused-import warning | No |

## Severity Summary

| Severity | Open count |
|---|---:|
| Critical | 0 |
| High | 1 |
| Medium | 2 |
| Low | 0 |

Closed this retest: REL-P4-002, REL-P4-003, REL-P4-004, REL-P4-007, REL-P4-008, REL-P4-009.

## Gate Summary

Final gate: `BLOCKED`

P4-BEG2 closed malformed-filter, export-validation, product CSRF, and self-deactivation defects. The gate still cannot pass while High defect REL-P4-001 fails `composer test:integration` and Medium defects REL-P4-005 and REL-P4-006 remain open.
