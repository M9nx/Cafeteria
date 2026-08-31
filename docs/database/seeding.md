# Database Seeding

Development-only commands for deterministic demo data after migrations.

## Commands

```bash
php database/migrate.php
php database/seed.php
php database/verify.php
php database/rebuild.php
```

| Command | Purpose |
|---------|---------|
| `migrate.php` | Apply pending SQL migrations |
| `seed.php` | Insert or upsert demo rooms, categories, products, and users |
| `verify.php` | Check row counts, foreign keys, prices, and admin password hash |
| `rebuild.php` | Drop all tables, migrate, seed, and verify (development/test only) |

## Seed order

Seeders run in dependency-safe order:

1. Rooms
2. Categories
3. Products
4. Users

## Idempotency

`php database/seed.php` may be run twice without creating duplicate rows.

- Rooms and categories upsert on unique `name`.
- Products skip insert when the same `(category_id, name)` already exists.
- Users upsert on unique `email`.

## Demo credentials (development only)

These accounts exist only for local and test databases. **Never use them in production.**

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@example.test` | `DevPassword123!` |
| User | `user@example.test` | `DevPassword123!` |

## Rebuild safety

`php database/rebuild.php` drops every table in the current database. It refuses to run when:

- `APP_ENV=production`, or
- `DB_NAME` does not end with `_dev` or `_test`

**Never run rebuild against production.**

## Verification evidence

For pull-request or issue evidence, capture output from:

```bash
php database/rebuild.php
php database/seed.php
php database/verify.php
```

A second `seed.php` run should report the same seeded entities without increasing row counts.
