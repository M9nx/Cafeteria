# Fixtures and Demo Data Plan

## Fixture Approach

Test fixtures provide consistent and reproducible data for automated tests.

Fixtures should be isolated from production data and should be reusable across test runs.

The test suite may use fixture data for users, products, categories, rooms, and orders.

Fixtures should cover different user roles, order statuses, and relevant business rules.

## Demo Data Plan

Demo data will be used for development and manual verification of the main application flows.

The demo dataset should include:

- One admin user
- At least one regular user
- Sample categories
- Sample products with different prices
- Seeded rooms
- Sample orders with different statuses

Demo data must be separated from production data and must not contain real user information or sensitive credentials.

## Evidence Requirements

Test and delivery evidence should be kept with the related issue or pull request.

Evidence may include:

- PHPUnit test output.
- Screenshots when they help verify UI behavior.
- Relevant documentation or migration evidence.
- Test results after addressing review comments.
Evidence should clearly show what was verified and should not contain secrets or sensitive user information.