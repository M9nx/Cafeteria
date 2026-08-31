# Git and Pull Request Workflow

This workflow describes the basic Git and Pull Request process for the project.

## 1. Create a branch

Create the feature or test branch from the current `main` branch.

The branch name should include the related issue number.

Example:

```text
test/7-traceability-test-foundation

## 2. Make changes

Implement the assigned work and keep changes focused on the issue.

## 3. Check the changes

Run:

```bash
git status

## 4. Commit changes

Use Conventional Commits.

Examples:

```bash
git add .
git commit -m "test: add acceptance test matrix"


## 5. Push the branch

Push the working branch to the remote repository.

Example:

```bash
git push -u origin <branch-name>

## 6. Open a Pull Request

Open a Pull Request from the working branch into `main`.

The PR should reference the issue, for example:

```text
Closes #7

## 7. Review

Request the required reviewers.

Address review comments and push the updates to the same branch.

## 8. Retest

Run the relevant tests and verification commands after changes or review fixes.

## 9. Merge

After approval and successful verification, the Pull Request can be merged according to the project workflow.