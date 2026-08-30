# Architecture — Cafeteria Management System

**Style:** Modular monolith in plain PHP 8.4.x — no full-stack framework, no microservices.

## Layer responsibilities

| Layer | Location | Responsibility |
|---|---|---|
| Front controller | `public/index.php` | Single HTTP entry; bootstrap and dispatch |
| Router | `app/Core/Routing/` | Match method + path; run middleware; 404/405 |
| Middleware | `app/Core/Auth/` (future) | Session, CSRF, authentication |
| Controllers | `app/Controllers/` | Authorize, validate/map input, call one service, respond |
| Services | `app/Services/` | Use cases, business rules, transactions |
| Repositories | `app/Repositories/` | PDO persistence behind interfaces |
| Domain | `app/Domain/` | Pure business concepts and invariants |
| DTO / Validation | `app/DTO/`, `app/Validation/` | Typed input at boundaries |
| Policies | `app/Policies/` | Role and ownership authorization |
| Views | `resources/views/` | Presentation only; escaped output |

## Request flow

```text
Browser
  → public/index.php
  → bootstrap/app.php (explicit dependencies)
  → Router::dispatch(Request)
  → Middleware chain (when registered)
  → Controller action
  → Service use case
  → Repository (PDO)
  → MySQL
  → Response (HTML or redirect)
```

Dependency direction is **inward**: Controllers depend on Services; Services depend on Repository contracts; Repositories depend on PDO. Views receive prepared data only — no SQL or authorization logic in templates.

## MVC boundaries

- **Model:** Domain entities, DTOs, and repository persistence — not fat Active Record on controllers.
- **View:** PHP templates under `resources/views/` rendered via `View::render()`.
- **Controller:** Thin HTTP adapter; no SQL, price math, or password hashing.

## Route loading

`routes/web.php` loads module route files when present so feature PRs do not edit one shared monolithic route file:

```php
foreach (['auth.php', 'orders.php', 'admin.php', 'reports.php'] as $file) {
    $path = __DIR__ . '/' . $file;
    if (is_file($path)) {
        require $path;
    }
}
```

Each module registers routes on the shared `$router` instance passed from bootstrap.

## Module map (ownership)

| Module | Controllers | Services | Repositories |
|---|---|---|---|
| Auth | `Controllers/Auth/` | `AuthService` | `UserRepository` |
| Users | `Controllers/Admin/` | `UserService` | `UserRepository` |
| Catalog | `Controllers/Admin/` | `CatalogService` | `ProductRepository`, `CategoryRepository` |
| Ordering | `Controllers/User/`, `Controllers/Admin/` | `OrderService` | `OrderRepository` |
| Fulfillment | `Controllers/Admin/` | `OrderStatusService` | `OrderRepository` |
| Reporting | `Controllers/Admin/` | `ReportService` | read repositories |

## Directory map

```text
app/           Application source (not web-accessible)
bootstrap/     Startup and dependency wiring
config/        Environment-driven configuration arrays
database/      Migrations, seeds, CLI scripts
docs/          Architecture, ADRs, test plans
public/        Document root — index.php and static assets only
resources/     View templates
routes/        Route declarations loaded by web.php
storage/       Logs and private uploads (not committed)
tests/         PHPUnit suites
```

## Patterns in use

- **Front Controller** — one entry point
- **MVC** — separate HTTP, presentation, and domain
- **Service Layer** — business workflows
- **Repository** — testable data access
- **Dependency Injection** — constructor injection from bootstrap container
- **DTO** — validated input objects
- **Policy** — centralized authorization

See ADRs in `docs/adr/` for foundational decisions.
