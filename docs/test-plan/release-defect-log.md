# Release Defect Log

P4-LEAD release triage for issue #51. This log records open defects and unavailable owner-delivery work discovered during the Day 5 security audit.

No defect is marked closed because this package is documentation-only and did not implement application-code fixes.

| Defect ID | Source finding/test | Severity | Description | Owner WBS ID | Branch/PR | Status | Fix evidence | Retest command | Retest result | Release blocker |
|---|---|---|---|---|---|---|---|---|---|---|
| REL-P4-001 | `SEC-P4-001`; `composer test:integration` | High | Integration suite fails in isolation because `Tests\Integration\Orders\OrderTransactionTest` imports `Tests\Unit\Services\FakeProductRepository`, which is not autoloaded when only the Integration suite runs. | `P3-BEG3` | Pending owner delivery | BLOCKED | None yet | `composer test:integration`; `composer test` | `composer test:integration` exit 2; full suite exit 0 with warning | Yes |
| REL-P4-002 | `SEC-P4-002`; manual dispatch `/admin/checks/users/2` | High | Required checks drill-down route/action is unavailable, so admin-only drill-down authorization and IDOR behavior cannot be verified. | `P4-INTR` | Pending owner delivery | BLOCKED | None yet | Report drill-down HTTP/security tests after P4-INTR delivery | Admin dispatch returned 404; no fix retest yet | Yes |
| REL-P4-003 | `SEC-P4-003`; manual dispatch `/admin/checks/export` | High | Required checks export route/service is unavailable, so export authorization, filter reuse, safe headers, and CSV formula-injection defense cannot be verified. | `P4-BEG2` | Pending owner delivery | BLOCKED | None yet | Report export authorization and CSV formula-injection tests after P4-BEG2 delivery | Guest/user/admin dispatches returned 404; no fix retest yet | Yes |
| REL-P4-004 | `SEC-P4-004`; malformed report filter dispatch | Medium | Report controller silently accepts or coerces malformed/duplicate `user_id` values and warns on array-shaped `include_cancelled`. | `P4-BEG2` | Pending owner delivery | OPEN | None yet | Report filter security tests for malformed IDs, duplicate parameters, array parameters, invalid dates, and SQLi-like strings | `user_id=abc`, `user_id=1abc`, duplicate `user_id`, and `include_cancelled[]=1` did not reject safely | Yes |
| REL-P4-005 | `SEC-P4-005`; session code review | Medium | Existing authenticated sessions are not revalidated against `users.is_active`, so deactivated users can retain authorization until session expiry/logout. | `P1-INTR` | Pending owner delivery | OPEN | None yet | Auth HTTP test for deactivated logged-in user followed by protected request | Not implemented; code path confirmed | Yes |
| REL-P4-006 | `SEC-P4-006`; upload code review | Medium | `ImageContentValidator` exists but is not called by `SafeUploader`, contradicting documented `getimagesize` enforcement on the storage path. | `P2-BEG2` | Pending owner delivery | OPEN | None yet | Upload spoofing tests plus `composer test` | Not fixed; code path confirmed | Yes |
| REL-P4-007 | `SEC-P4-007`; product CSRF dispatch | Medium | Product admin forms render `_token`, while `ProductController` validates `_csrf_token`, blocking legitimate product create/update/deactivate submissions. | `P2-BEG2` | Pending owner delivery | OPEN | None yet | Product create/update/deactivate HTTP tests with rendered CSRF token | Dispatch with `_token` raised `RuntimeException: Invalid CSRF token.` | Yes |
| REL-P4-008 | `SEC-P4-008`; rollback self-deactivation probe | Medium | Admin self-deactivation is allowed despite the existing checklist claiming it is blocked. | `P2-BEG2` | Pending owner delivery | OPEN | None yet | Admin abuse test for self-deactivation and last-active-admin deactivation | Rollback probe changed admin `id=1` from active to inactive inside transaction | Yes |
| REL-P4-009 | `SEC-P4-009`; PHP lint/PHPUnit warning | Low | `bootstrap/app.php:58` has an unused `DateTimeZone` import that triggers a repeatable PHP warning in lint and tests. | `P4-INTR` | Pending owner delivery | OPEN | None yet | `php -l bootstrap/app.php`; `composer check` | Warning still present | No |

## Severity Summary

| Severity | Open count |
|---|---:|
| Critical | 0 |
| High | 3 |
| Medium | 5 |
| Low | 1 |

## Gate Summary

Final gate: `BLOCKED`

The next review package must not mark the gate passing until all High defects are either fixed and retested or formally accepted by the release owner, and required regression/security tests complete successfully.
