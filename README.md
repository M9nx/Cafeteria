# Cafeteria Management System

Pure PHP 8.4 modular monolith with MySQL for cafeteria ordering: users browse a catalogue, place and track orders; admins manage catalog, users, fulfillment, and reports.

No framework. Document root is `public/` only ([ADR 0002](docs/adr/0002-public-document-root.md)).

**Master reference:** [Issue #1](https://github.com/M9nx/Cafeteria/issues/1)

## Current state

The app is past the Day 1 scaffold. Local setup can run:

- Login, logout, and password reset (`MAIL_DRIVER=log` writes mail to logs)
- Catalogue with product images (public assets plus hashed uploads under `storage/uploads/`, served at `/media/...`)
- User orders: cart, checkout, history, cancel while `PROCESSING`
- Admin: users, products, categories, orders on behalf of a user, current queue, reports and CSV export

Uploads, logs, and `.env` stay outside the web root. See [docs/architecture.md](docs/architecture.md) and [docs/scope.md](docs/scope.md).

## Prerequisites

- PHP 8.4+ CLI with `pdo` and `pdo_mysql`
- Composer
- MySQL 8.4 (utf8mb4)

## Setup

Create the development database (and a test database if you will run PHPUnit):

```sql
CREATE DATABASE cafeteria_dev
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

CREATE DATABASE cafeteria_test
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;
```

Install dependencies, copy environment, then migrate and seed:

```bash
composer install
cp .env.example .env
# edit .env: DB_* credentials, APP_URL, and mail if you use SMTP
composer migrate
composer seed
```

`composer migrate` applies pending files in `database/migrations/`. `composer seed` is idempotent: rooms, categories, products, and demo users can be seeded again without duplicate rows. Placeholder product images are replaced with the demo artwork; existing uploads are left alone.

Point `APP_URL` and the PHP built-in server at the same host and port (`.env.example` uses `http://127.0.0.1:8000`):

```bash
php -S 127.0.0.1:8000 -t public
```

Health check (no login):

```bash
curl -i http://127.0.0.1:8000/health
# HTTP/1.1 200 OK ... OK
```

Open `/` and sign in. Full seed notes, including rebuild safety: [docs/database/seeding.md](docs/database/seeding.md).

### Demo accounts (local / test only)

Never use these in production.

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@example.test` | `DevPassword123!` |
| User | `user@example.test` | `DevPassword123!` |

## Commands

Composer scripts (`composer.json`):

| Command | What it does |
|---------|----------------|
| `composer install` | Install PHP dependencies |
| `composer migrate` | Run `php database/migrate.php` against `.env` (or OS env) |
| `composer seed` | Run `php database/seed.php` |
| `composer test` | Full PHPUnit suite |
| `composer test:unit` | Unit tests |
| `composer test:integration` | Integration tests |
| `composer test:feature` | Feature tests |
| `composer lint` | `php -l` on app, bootstrap, config, database, public, routes, tests |
| `composer check` | Validate Composer metadata, lint, then test |

Database CLI (same as Composer for migrate/seed; extra scripts are not Composer aliases):

```bash
php database/migrate.php
php database/seed.php
php database/verify.php
php database/rebuild.php
```

| Script | Purpose |
|--------|---------|
| `database/migrate.php` | Apply pending SQL migrations |
| `database/seed.php` | Insert or upsert demo rooms, categories, products, users |
| `database/verify.php` | Check seed counts, foreign keys, prices, and admin password hash |
| `database/rebuild.php` | Drop all tables, then migrate, seed, and verify |

`rebuild.php` refuses `APP_ENV=production` and any `DB_NAME` that does not end with `_dev` or `_test`. It destroys the current database. Never run it against production.

OS environment wins over `.env`. To migrate and seed the PHPUnit database without editing `.env`:

```bash
DB_NAME=cafeteria_test composer migrate
DB_NAME=cafeteria_test composer seed
```

Match `phpunit.xml` `DB_USER` / `DB_PASSWORD` to that database (defaults: `root` with an empty password). CI uses `cafeteria_test` with `root` / `root`.

## Tests

Feature and integration tests need `cafeteria_test` migrated and seeded first.

```bash
composer test
# or: composer check
```

## Architecture

- **Entry:** `public/index.php` → `bootstrap/app.php` → Router → Controller → Service → Repository
- **Routes:** `routes/web.php` registers `/media/{kind}/{filename}` and loads `auth.php`, `orders.php`, and `admin.php` (reports live under admin routes)
- **Style:** Modular monolith MVC ([ADR 0001](docs/adr/0001-modular-monolith-mvc.md))

## Day guides

Beginner walkthroughs of what each phase built, plus a picture-first atlas of the live system:

| Document | Phase | Contents |
|----------|-------|----------|
| [Day 1 foundation](docs/day-1-foundation-guide.md) | P0 | HTTP stack, migrations, seeds, CI |
| [Day 2 authentication and admin](docs/day-2-authentication-admin-guide.md) | P1 | Session, CSRF, login/reset, users/categories |
| [Day 3 catalog and ordering](docs/day-3-catalog-ordering-guide.md) | P2 | Catalogue, cart, placement, snapshots |
| [Day 4 order lifecycle](docs/day-4-order-lifecycle-guide.md) | P3 | History, cancel, queue, on-behalf |
| [Day 5 reporting and security](docs/day-5-reporting-security-guide.md) | P4 | Checks, drill-down, CSV, mailer |
| [Architecture atlas through Day 5](docs/system-through-day-5-architecture-guide.md) | P0–P4 | Numbered C4/DFD/sequences (Figures 1–23), current `main` |
| [Request lifecycle & codebase atlas](docs/request-lifecycle-and-codebase-atlas.md) | All phases | HTTP lifecycle, DI, file purposes, classes/methods |
| [Database & schema guide](docs/database-schema-guide.md) | P0–P4 | Tables, constraints, indexes, ER |

## Contributing

[CONTRIBUTING.md](CONTRIBUTING.md) covers branch names, Conventional Commits, PRs, and review ownership.

## Project links

| Resource | URL |
|---|---|
| Master issue | https://github.com/M9nx/Cafeteria/issues/1 |
| Workbook | [Google Sheets WBS](https://docs.google.com/spreadsheets/d/1Utz2Ijn-ZSj5UN5EhxXY05H3lkVgSBWV44bp7ofFWFA/edit?gid=1729829340#gid=1729829340) |
