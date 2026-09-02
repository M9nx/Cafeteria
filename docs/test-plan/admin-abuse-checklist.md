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
