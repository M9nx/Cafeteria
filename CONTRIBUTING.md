# Contributing — Cafeteria Management System

This document defines the team workflow for the six-day delivery plan. Every member follows the same rules so work stays traceable from issue to merge.

## Branch names

Use the **leaf GitHub issue number**, not the phase issue:

```text
feat/<ISSUE_NUMBER>-short-name
fix/<ISSUE_NUMBER>-short-name
test/<ISSUE_NUMBER>-short-name
chore/<ISSUE_NUMBER>-short-name
docs/<ISSUE_NUMBER>-short-name
```

Examples: `feat/5-shared-bootstrap-layout`, `chore/3-foundation-governance`.

Never create a branch for a vague phase issue alone.

## Commits

Use [Conventional Commits](https://www.conventionalcommits.org/):

```text
feat(order): add price snapshots to order items
fix(auth): reject inactive users during login
docs(setup): document database migration command
```

Keep commits small, focused, and compilable. One logical change per commit.

## Pull requests

1. One leaf issue per PR unless issues are inseparable and documented.
2. PR title follows commit style; body must include `Closes #<ISSUE_NUMBER>`.
3. Include screenshots for UI changes and test output for behavior changes.
4. Complete the self-review checklist in the PR template before requesting review.
5. At least one reviewer approves; the author cannot approve their own PR.
6. Architecture, security, and database changes require the team lead's review.
7. CI must pass and all review conversations must be resolved.
8. Merge using a **merge commit** for clear integration evidence; delete the remote feature branch afterward.
9. No direct pushes to `main`, no force-push to shared branches, no merging broken code.

## Review ownership

| Team member | Review rule |
|---|---|
| LEAD-Mounir Sabry | Own PRs require Salma and Hana approvals |
| INTR-Salma Fathy | Mounir plus assigned second reviewer |
| BEG1-Taghreed Mohamed | Mounir plus Salma |
| BEG2-Basha Wahed | Mounir plus Salma |
| BEG3-Hana Elsayed | Mounir plus Salma |

## Five-hour WBS evidence

Each work-package issue closes only when:

- Learning evidence is linked and the workbook Learning Gate is `READY`.
- Planned and actual hours are recorded honestly (target: 5 hours per package).
- Acceptance criteria pass with attached delivery evidence.
- PR is approved, merged, and verified from `main`.

## Definition of Done

- Acceptance criteria pass on `main` after merge.
- Tests and manual checks relevant to the package pass.
- Workbook WBS row matches GitHub issue, branch, PR, status, and evidence fields.
- No secrets, personal data, or generated runtime artifacts are committed.

## Daily developer flow

```bash
git switch main
git pull --ff-only origin main
git switch -c feat/<ISSUE_NUMBER>-short-name
# work, test, commit
git push -u origin feat/<ISSUE_NUMBER>-short-name
# open PR, address review, merge through GitHub
```

If a branch falls behind `main`, merge `origin/main` into the feature branch and resolve conflicts there — never on `main`.

## Protected `main` branch

Configure on GitHub (Settings → Branches → Branch protection rules):

- Require pull requests before merging.
- Require at least one approval; dismiss stale approvals on new pushes.
- Require status checks to pass (CI workflow when available).
- Require conversation resolution before merging.
- Block force pushes and branch deletion.
- Restrict who can push directly (team lead only for emergencies, documented in issue comments).
