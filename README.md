# Cafeteria Management System

Pure PHP 8.4 modular monolith for cafeteria ordering — course project with GitHub workflow evidence.

**Master reference:** [Issue #1](https://github.com/M9nx/Cafeteria/issues/1)  
**Day 1 phase:** [Issue #2](https://github.com/M9nx/Cafeteria/issues/2)

> This README is the Day 1 setup entry point. Final documentation is completed on Day 6 (P5-BEG3).

## Prerequisites

- PHP 8.4 CLI
- Composer (added in P0-INTR)
- MySQL 8.4 (added in P0-INTR)

## Day 1 — runnable baseline (P0-LEAD)

The foundation scaffold runs without a framework:

```bash
php -S 127.0.0.1:8000 -t public
```

Verify the health endpoint:

```bash
curl -i http://127.0.0.1:8000/health
# Expected: HTTP/1.1 200 OK ... OK
```

## Full setup (after P0-INTR merges)

```bash
composer install
cp .env.example .env
# edit .env with local database credentials
php database/migrate.php
php database/seed.php
php -S 127.0.0.1:8000 -t public
```

## Tests (after P0-BEG3 / P0-INTR)

```bash
composer test
```

## Architecture summary

- **Entry:** `public/index.php` → `bootstrap/app.php` → Router → Controller → Service → Repository
- **Document root:** `public/` only ([ADR 0002](docs/adr/0002-public-document-root.md))
- **Style:** Modular monolith MVC ([ADR 0001](docs/adr/0001-modular-monolith-mvc.md))

See `docs/architecture.md` and `docs/scope.md` for full details.

## Contributing

Read [CONTRIBUTING.md](CONTRIBUTING.md) for branch names, commits, PR rules, and review policy.

## Project links

| Resource | URL |
|---|---|
| Master issue | https://github.com/M9nx/Cafeteria/issues/1 |
| P0 phase | https://github.com/M9nx/Cafeteria/issues/2 |
| Workbook | Google Sheets WBS (team access) |
