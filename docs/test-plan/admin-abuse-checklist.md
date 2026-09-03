# Admin CRUD Manual Abuse Checklist

Manual checks for P2-BEG2 (#30). Run while logged in as an active admin user after seeding demo data.

## Layout and navigation

| # | Case | Steps | Expected | Result |
|---|------|-------|----------|--------|
| L1 | Navbar on categories index | Open `/admin/categories` | Shared navbar, CSRF logout, page title in layout | PASS |
| L2 | Navbar on users index | Open `/admin/users` | Same authenticated shell as catalogue | PASS |
| L3 | Navbar on products index | Open `/admin/products` | Same authenticated shell as catalogue | PASS |
| L4 | Navbar on create/edit forms | Open each admin create and edit form | Layout shell with navbar; no standalone HTML document | PASS |
| L5 | Non-admin blocked | Log in as `USER`, request `/admin/products` | Redirect or 403; admin pages not rendered | PASS (AuthMiddleware + AdminMiddleware) |

## CSRF

| # | Case | Steps | Expected | Result |
|---|------|-------|----------|--------|
| C1 | Missing token on category create | Submit `POST /admin/categories` without `_csrf_token` | Request rejected; safe error | PASS |
| C2 | Missing token on product deactivate | Submit deactivate without token | Request rejected | PASS |
| C3 | Logout CSRF | Use navbar logout form | Token field present; logout succeeds | PASS |

## Category CRUD abuse

| # | Case | Steps | Expected | Result |
|---|------|-------|----------|--------|
| A1 | Empty name | Submit create with blank name | Validation error; no row created | PASS |
| A2 | Duplicate name | Create category with existing name | Safe validation message | PASS |
| A3 | Deactivate twice | Deactivate same category twice | Second attempt handled safely | PASS |
| A4 | Edit inactive toggle | Uncheck Active on edit form | Category saved inactive | PASS |

## User CRUD abuse

| # | Case | Steps | Expected | Result |
|---|------|-------|----------|--------|
| U1 | Invalid email | Submit create with malformed email | Validation error | PASS |
| U2 | Short password on create | Password under 8 chars | Validation error | PASS |
| U3 | Self-deactivate | Attempt deactivate on logged-in admin | Blocked with safe message | PASS |
| U4 | Oversized avatar | Upload image > 2 MB | Rejected with size message | PASS |
| U5 | Spoofed MIME | Upload `.php` renamed to `.jpg` | Rejected (finfo + getimagesize) | PASS |

## Product CRUD abuse

| # | Case | Steps | Expected | Result |
|---|------|-------|----------|--------|
| P1 | Zero price | Submit price `0` | Validation error | PASS |
| P2 | Missing category | Submit without category | Validation error | PASS |
| P3 | Invalid image type | Upload `.txt` as image | Rejected with safe message | PASS |
| P4 | Spoofed PNG header | Non-image bytes with PNG MIME | Rejected after content inspection | PASS |
| P5 | Wrong CSRF field name | Submit with `_token` instead of `_csrf_token` | Rejected (fixed Day-2 defect) | PASS |

## Upload hardening notes

- Server validates upload size (max 2 MB), `is_uploaded_file`, finfo MIME, and `getimagesize` content match.
- Stored filenames are random hex; original client names are not used on disk.
- Error messages do not expose stack traces or internal paths.

## Evidence

- Before: admin pages rendered standalone HTML without navbar (Day 2 baseline).
- After: all admin index/create/edit views render through `layouts/app.php` with `$currentUser`.
- Upload rejection: oversized and spoofed files return user-safe validation messages in form error summary.

Recorded by: Basha Wahed  
Date: 2026-09-02

## Day 5 reporting and release-gate abuse cases

Run date: 2026-09-03

Audited commit: `679763765603b0d8efbe74d4d538703f5274d93d`

Evidence source: P4-LEAD in-process dispatch probes and code review in `docs/security/day-5-security-audit.md`.

| # | Preconditions | Request/action | Expected result | Actual result | Result | Evidence | Defect ID |
|---|---|---|---|---|---|---|---|
| R1 | No authenticated session | `GET /admin/checks` | Redirect to login; report page not rendered | `302` with `Location: /login` | PASS | Manual dispatch: `guest checks \| status=302 \| location=/login` | None |
| R2 | Authenticated normal `USER` session | `GET /admin/checks` | Denied with 403; report page not rendered | `403` with body prefix `Forbidden` | PASS | Manual dispatch: `user checks \| status=403` | None |
| R3 | Authenticated admin; another user's ID exists | `GET /admin/checks/users/2` | Admin-only drill-down route enforces report scope and does not expose unrelated data | Route returned `404 Not Found`; drill-down is not implemented | BLOCKED | Manual dispatch: `admin drilldown \| status=404` | REL-P4-002 |
| R4 | Authenticated admin; user ID is not present | `GET /admin/checks?user_id=999999` | Safe validation error; no report rows for invalid user | Status `200` with `The selected user does not exist.` | PASS | Manual dispatch: `unknown user \| expected_text_present=yes` | None |
| R5 | Authenticated admin | `GET /admin/checks?user_id=abc` and `GET /admin/checks?user_id=1abc` | Malformed IDs rejected with validation error | Status `200`; expected validation text absent; `abc` was ignored and `1abc` was coerced | FAIL | Manual dispatch: `malformed user abc/1abc \| expected_text_present=no` | REL-P4-004 |
| R6 | Authenticated admin | `GET /admin/checks?from=2026-02-01&to=2026-01-01` | Reversed range rejected safely | Status `200` with `From date must not be after to date.` | PASS | Manual dispatch: `reversed date \| expected_text_present=yes` | None |
| R7 | Authenticated admin | `GET /admin/checks?from=2026-99-99` | Invalid date rejected safely | Status `200` with `From date must be in YYYY-MM-DD format.` | PASS | Manual dispatch: `invalid from \| expected_text_present=yes` | None |
| R8 | Authenticated admin | Excessive date range | If a max range exists, reject with validation error | No max report date range is documented or implemented | BLOCKED | `ChecksFilterValidator` validates format/order only | None |
| R9 | Authenticated admin | `GET /admin/checks?include_cancelled[]=1` | Array-shaped toggle rejected or normalized without warning | Status `200`; PHP warning `Array to string conversion` at `ReportController.php:30` | FAIL | Manual dispatch: `manipulated include array` | REL-P4-004 |
| R10 | Authenticated admin | `GET /admin/checks?user_id=999999&user_id=abc` | Duplicate parameters rejected or deterministically validated | Status `200`; expected invalid-user text absent after PHP retained the last value | FAIL | Manual dispatch: `duplicate user id \| expected_text_present=no` | REL-P4-004 |
| R11 | Authenticated admin | SQL injection strings in `user_id`, `from`, or `to` filters | SQL structure unchanged; malformed values rejected safely | SQL did not error or change structure, but malformed `user_id` values were accepted/coerced | FAIL | Code review: report values are bound; manual malformed-ID evidence failed validation expectation | REL-P4-004 |
| R12 | Authenticated admin | XSS string in report filter: `<script>alert(1)</script>` | Raw payload not rendered; escaped value only | Status `200`; raw script absent; escaped script present | PASS | Manual dispatch: `raw_script=no \| escaped_script=yes` | None |
| R13 | No authenticated session | `GET /admin/checks/export` | If implemented, redirect to login through admin/auth middleware | Route returned `404 Not Found`; export is not implemented | BLOCKED | Manual dispatch: `guest export \| status=404` | REL-P4-003 |
| R14 | Authenticated normal `USER` session | `GET /admin/checks/export` | If implemented, denied with 403 | Route returned `404 Not Found`; export is not implemented | BLOCKED | Manual dispatch: `user export \| status=404` | REL-P4-003 |
| R15 | Authenticated admin | Export URL with tampered filters | Export reuses validated report filters and rejects malformed inputs | Export route/service unavailable | BLOCKED | `/admin/checks/export` returned 404 | REL-P4-003 |
| R16 | Authenticated admin; report data contains cells beginning with `=`, `+`, `-`, or `@` | Export CSV | Formula-leading CSV cells are neutralized | CSV export service unavailable | BLOCKED | No `ReportExportService`; `/admin/checks/export` returned 404 | REL-P4-003 |
| R17 | Authenticated admin; report filters applied | Navigate summary to drill-down and export | `user_id`, `from`, `to`, and `include_cancelled` are preserved consistently | Drill-down and export links/routes unavailable | BLOCKED | `resources/views/admin/reports/index.php` renders summary table only | REL-P4-002; REL-P4-003 |

## Day 5 additional regression notes

- Product admin CSRF forms still render `_token` while `ProductController` expects `_csrf_token`; see REL-P4-007.
- Admin self-deactivation is allowed in the current implementation; see REL-P4-008.
- Upload hardening notes above claim `getimagesize` content matching, but `SafeUploader` does not call `ImageContentValidator`; see REL-P4-006.
