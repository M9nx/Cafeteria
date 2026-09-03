# Cafeteria Management System — Architecture Atlas (through Day 5)

**Version:** 1.0  
**Date:** 3 September 2026  
**Audience:** Reviewers, new team members, and anyone who needs the live system in pictures  
**Scope:** The application **as it exists after Days 1–5**, including later small fixes (public media URLs, users list showing USER and ADMIN)  
**Prose companions:** [Day 1](day-1-foundation-guide.md) · [Day 2](day-2-authentication-admin-guide.md) · [Day 3](day-3-catalog-ordering-guide.md) · [Day 4](day-4-order-lifecycle-guide.md) · [Day 5](day-5-reporting-security-guide.md)

> This is a **visual system atlas**, not a repeat of the day guides. Every diagram matches code under `app/`, `public/`, `routes/`, and `bootstrap/app.php`.

---

## Table of contents

1. [How to read this atlas](#1-how-to-read-this-atlas)
2. [C4 context](#2-c4-context)
3. [C4 containers](#3-c4-containers)
4. [C4 components — HTTP container](#4-c4-components--http-container)
5. [C4 code — selected internals](#5-c4-code--selected-internals)
6. [Layer atlas](#6-layer-atlas)
7. [Composition root (the PHP container)](#7-composition-root-the-php-container)
8. [DFD level 0 — whole system](#8-dfd-level-0--whole-system)
9. [DFD level 1 — major processes](#9-dfd-level-1--major-processes)
10. [DFD level 2 — place order and export](#10-dfd-level-2--place-order-and-export)
11. [Queue and state graphs](#11-queue-and-state-graphs)
12. [HTTP dispatch pipeline](#12-http-dispatch-pipeline)
13. [Sequence — authentication](#13-sequence--authentication)
14. [Sequence — catalogue and place order](#14-sequence--catalogue-and-place-order)
15. [Sequence — history, detail, cancel](#15-sequence--history-detail-cancel)
16. [Sequence — fulfillment queue](#16-sequence--fulfillment-queue)
17. [Sequence — admin on-behalf](#17-sequence--admin-on-behalf)
18. [Sequence — CRUD, uploads, media](#18-sequence--crud-uploads-media)
19. [Sequence — checks, drill-down, export](#19-sequence--checks-drill-down-export)
20. [Sequence — authorization boundaries](#20-sequence--authorization-boundaries)
21. [Trust boundaries](#21-trust-boundaries)
22. [Route atlas](#22-route-atlas)
23. [Data model](#23-data-model)
24. [Class inventory](#24-class-inventory)
25. [Diagram index](#25-diagram-index)

---

## 1. How to read this atlas

C4 levels used here:

| Level | Question | Section |
|-------|----------|---------|
| Context | Who talks to the system? | §2 |
| Container | What processes and stores run? | §3 |
| Component | What is inside the PHP HTTP process? | §4 |
| Code | Which classes implement a hot path? | §5 |

DFDs show **data**, not HTTP methods. Sequences show **time**. State diagrams show **order status**.

There is **no message queue product** (no Redis/RabbitMQ). “Queue” in this product means the **admin current-order list**.

---

## 2. C4 context

People and systems that talk to Cafeteria. Cursor Markdown Preview does not support Mermaid `C4Context`, so this is a C4 **context** view drawn as a flowchart.

```mermaid
flowchart LR
  User["Cafeteria user"]
  Admin["Administrator"]
  App["Cafeteria PHP app"]
  MySQL[("MySQL 8.4")]
  Mail["Log or SMTP"]
  User -->|"HTTP"| App
  Admin -->|"HTTP"| App
  App -->|"PDO"| MySQL
  App -->|"MailerInterface"| Mail
```

External systems we do **not** have: payment gateway, inventory ERP, public REST clients.

---

## 3. C4 containers

This is the “go into container” view: runnable units, not Docker (the course app is PHP CLI + built-in server).

```mermaid
flowchart TB
  subgraph browser ["Container: Browser"]
    HTML["HTML views"]
    JS["cart.js / order-details.js / reports.js"]
    SS["sessionStorage cart"]
    HTML --> JS
    JS --> SS
  end
  subgraph httpProc ["Container: PHP HTTP"]
    Pub["public/index.php"]
    Boot["bootstrap/app.php"]
    Rtr["Router"]
    Pub --> Boot --> Rtr
  end
  subgraph cli ["Container: PHP CLI"]
    Mig["migrate.php"]
    Seed["seed.php"]
    Ver["verify.php"]
    Reb["rebuild.php"]
  end
  subgraph fs ["Container: Filesystem"]
    Up["storage/uploads"]
    Logs["storage/logs"]
  end
  subgraph db ["Container: MySQL"]
    Tables["rooms, users, products, orders"]
  end
  subgraph mailc ["Container: Mail"]
    LogM["LogMailer"]
    SmtpM["SmtpMailer"]
  end
  browser -->|"cookies and forms"| httpProc
  httpProc --> db
  httpProc --> Up
  httpProc --> Logs
  httpProc --> mailc
  cli --> db
```

| Container | Technology | Responsibility |
|-----------|------------|----------------|
| Browser | HTML + Bootstrap 5 + small JS | Presentation, cart preview only |
| PHP HTTP | PHP 8.4, `php -S -t public` | All use cases |
| PHP CLI | Same app classes | Schema and demo data |
| MySQL | 8.4 utf8mb4 | Source of truth |
| Filesystem | `storage/` outside document root | Hashed uploads, logs |
| Mail | `MAIL_DRIVER` | Reset links |

**Document root:** only [public/](../public/). See [ADR 0002](adr/0002-public-document-root.md).

---

## 4. C4 components — HTTP container

```mermaid
flowchart TB
  subgraph entry [Front]
    Idx[index.php]
    Req[Request]
    Res[Response]
  end
  subgraph pipe [Routing]
    Router[Router]
    AuthMW[AuthMiddleware]
    AdminMW[AdminMiddleware]
    GuestMW[GuestMiddleware]
  end
  subgraph httpc [Controllers]
    AuthC[Auth controllers]
    CatC[CatalogController]
    OrdC[OrderController]
    AdmC["Admin CRUD fulfillment reports"]
    MedC[MediaController]
    Health[HealthController]
  end
  subgraph appL [Application]
    Pol[Policies]
    Svc[Services]
    Val["Validators and DTOs"]
    Dom[Domain]
  end
  subgraph persist [Persistence]
    RepoI[Repository interfaces]
    RepoP[PDO implementations]
    PdoNode[PDO]
  end
  subgraph pres [Presentation]
    View["View render"]
    Tpl["resources/views"]
  end
  Idx --> Req --> Router
  Router --> AuthMW
  Router --> AdminMW
  Router --> GuestMW
  AuthMW --> httpc
  AdminMW --> httpc
  GuestMW --> httpc
  httpc --> Val
  httpc --> Svc
  httpc --> View
  Svc --> Pol
  Svc --> Dom
  Svc --> RepoI
  RepoI --> RepoP --> PdoNode
  View --> Tpl --> Res
  MedC --> UpFiles["storage/uploads"]
```

`MediaController` is intentionally **not** behind auth so `<img src="/media/...">` can load. Filenames are 32-hex plus a safe extension; path traversal is rejected.

---

## 5. C4 code — selected internals

```mermaid
flowchart LR
  subgraph httpObj [HTTP objects]
    ReqN["Request"]
    ResN["Response"]
    RtrN["Router"]
    RteN["Route"]
  end
  subgraph session [Session]
    SM["SessionManager"]
    CSRF["CsrfTokenManager"]
    FB["FlashBag"]
    AU["AuthenticatedUser"]
  end
  subgraph money [Ordering domain]
    Mon["Money"]
    OL["OrderLine"]
    OSt["OrderStatus"]
    OTM["OrderTransitionMatrix"]
  end
  subgraph upload [Uploads]
    SU["SafeUploader"]
    ICV["ImageContentValidator"]
    PFU["PublicFileUrl"]
  end
```

Hot-path rules:

- **Money** stores integer cents; no floats ([ADR 0004](adr/0004-order-pricing-snapshots.md)).
- **CSRF** synchronizer token `_csrf_token` ([ADR 0003](adr/0003-session-csrf-policy.md)).
- **Transitions** only via the matrix ([ADR 0005](adr/0005-order-state-machine.md)).
- **Reports** share one filter object ([ADR 0006](adr/0006-reporting-security-hardening.md)).

---

## 6. Layer atlas

Dependency direction is **inward**: controllers → services → contracts → PDO. Views receive arrays, never PDO.

```mermaid
flowchart BT
  Views[Views]
  Ctrl[Controllers]
  Svc[Services]
  Pol[Policies]
  Dom[Domain]
  Val[DTO + Validation]
  Repo[Repositories]
  PDO[(PDO MySQL)]
  Views --> Ctrl
  Ctrl --> Svc
  Ctrl --> Val
  Svc --> Pol
  Svc --> Dom
  Svc --> Repo
  Repo --> PDO
```

### 6.1 Controllers

| Class | Module |
|-------|--------|
| `HealthController` | Ops |
| `MediaController` | Files |
| `LoginController`, `LogoutController`, `ForgotPasswordController`, `ResetPasswordController` | Auth |
| `CatalogController` | Catalogue |
| `OrderController` | User orders |
| `UserController`, `CategoryController`, `ProductController` | Admin CRUD |
| `FulfillmentController`, `AdminOrderController` | Fulfillment / on-behalf |
| `ReportController` | Checks |

Admin HTML goes through `RendersAdminView` + `layouts.app`.

### 6.2 Services

`AuthService`, `PasswordResetService`, `UserService`, `CategoryService`, `ProductService`, `OrderService`, `OrderStatusService`, `UserOrderQueryService`, `ReportQueryService`, `ReportExportService`.

### 6.3 Domain

`Role`, `User`, `Money`, `OrderLine`, `OrderStatus`, `OrderTransitionMatrix`.

### 6.4 Policies

`AdminPolicy` (manage users/catalog/reports), `OrderPolicy` (view/cancel/transition).

### 6.5 Views and assets

Layouts: `layouts.app`, `layouts.guest`. User: catalog, orders. Admin: users, categories, products, orders queue/create, reports. JS: `cart.js` (preview only), `order-details.js`, `reports.js` (presentation), `app.js`.

---

## 7. Composition root (the PHP container)

There is **no** Symfony/Laravel container. [bootstrap/app.php](../bootstrap/app.php) builds a `$controllers` map of `class-string => object` and a `Router::setControllerFactory`.

```mermaid
flowchart TD
  Env[.env + config/*] --> PDO[ConnectionFactory]
  Env --> Sess[SessionManager]
  PDO --> Repos[PDO repositories]
  Repos --> Svcs[Services]
  Sess --> CSRF[CsrfTokenManager]
  CSRF --> Ctrl[Controller instances]
  Svcs --> Ctrl
  Ctrl --> Map[controllers array]
  Map --> Factory[Router factory]
```

CLI scripts (`migrate.php`, `seed.php`) load Environment + PDO themselves; they do not dispatch HTTP.

---

## 8. DFD level 0 — whole system

```mermaid
flowchart LR
  U[User]
  A[Admin]
  G[Guest]
  SYS((Cafeteria system))
  D[(MySQL)]
  F[Upload files]
  M[Mail]
  G -->|login/reset| SYS
  U -->|orders| SYS
  A -->|admin + checks| SYS
  SYS --> D
  SYS --> F
  SYS --> M
```

---

## 9. DFD level 1 — major processes

```mermaid
flowchart TB
  subgraph P1 [P1 Auth]
    Login[Authenticate]
    Reset[Reset password]
  end
  subgraph P2 [P2 Catalog]
    Browse[Browse products]
    Place[Place order]
  end
  subgraph P3 [P3 Lifecycle]
    Hist[History]
    Q[Fulfillment queue]
    Ob[On-behalf]
  end
  subgraph P4 [P4 Checks]
    Sum[Summarize]
    Exp[Export CSV]
  end
  subgraph AdminCRUD [Admin master data]
    Users[Users]
    Cats[Categories]
    Prods[Products]
  end
  DB[(MySQL)]
  Login --> DB
  Place --> DB
  Hist --> DB
  Q --> DB
  Sum --> DB
  Users --> DB
```

Processes talk to **one** logical data store (MySQL). Uploads are a second store used only by product/profile images.

---

## 10. DFD level 2 — place order and export

### 10.1 Place order

```mermaid
flowchart LR
  Cart[Cart in sessionStorage]
  Form[POST items room notes]
  Val[PlaceOrderValidator]
  OS[OrderService]
  Load[Load available products]
  Money[Build OrderLines]
  TX[PDO transaction]
  Ord[(orders)]
  It[(order_items snapshots)]
  Cart --> Form --> Val --> OS --> Load --> Money --> TX
  TX --> Ord
  TX --> It
```

Client total is **not** an input to `OrderService`.

### 10.2 Report export

```mermaid
flowchart LR
  Qs[Query string]
  Scal[Scalar parse]
  CF[ChecksFilter]
  CV[ChecksFilterValidator]
  SQ[Summarize SQL]
  CSV[CSV + formula prefix]
  File[Download]
  Qs --> Scal --> CF --> CV --> SQ --> CSV --> File
```

---

## 11. Queue and state graphs

### 11.1 Order status machine

```mermaid
stateDiagram-v2
  [*] --> PROCESSING: place order
  PROCESSING --> OUT_FOR_DELIVERY: admin hop
  OUT_FOR_DELIVERY --> DONE: admin hop
  PROCESSING --> CANCELLED: cancel if processing
```

Current queue includes `PROCESSING` and `OUT_FOR_DELIVERY` only. `DONE` and `CANCELLED` are excluded from the queue; cancelled orders are excluded from default money reports.

### 11.2 Current queue as a set

```mermaid
flowchart TB
  All[All orders] --> F{status}
  F -->|PROCESSING| Q[Current queue]
  F -->|OUT_FOR_DELIVERY| Q
  F -->|DONE| Out[Hidden from queue]
  F -->|CANCELLED| Out
```

Admin POST `/admin/orders/{id}/status` only for rows still in Q.

### 11.3 Status history

Each successful cancel or hop appends `order_status_history`. Failed races write **nothing**.

---

## 12. HTTP dispatch pipeline

Not a job queue — one request, one response.

```mermaid
sequenceDiagram
  participant S as PHP built-in / web server
  participant I as public/index.php
  participant B as bootstrap/app.php
  participant R as Router
  participant M as Middleware
  participant C as Controller
  S->>I: REQUEST_URI
  I->>B: require app
  B->>R: dispatch Request
  R->>R: match method + path
  R->>M: Auth / Admin / Guest
  alt middleware returns Response
    M-->>S: 302 / 401 / 403
  else continue
    M->>C: action + route params
    C-->>S: Response send
  end
```

404 = no path. 405 = path exists, method does not.

---

## 13. Sequence — authentication

```mermaid
sequenceDiagram
  actor Guest
  participant GM as GuestMiddleware
  participant LC as LoginController
  participant AS as AuthService
  participant SM as SessionManager
  participant DB as users
  Guest->>GM: POST /login + CSRF
  GM->>LC: not already logged in
  LC->>AS: attempt credentials
  AS->>DB: active user by email
  alt ok
    AS->>SM: regenerate session
    LC-->>Guest: 302 /
  else fail
    LC-->>Guest: generic error
  end
```

Forgot/reset: `PasswordResetService` stores **SHA-256 of token**, sends via `MailerInterface`, consumes token once.

Logout: `POST /logout` + CSRF → session destroy → login.

---

## 14. Sequence — catalogue and place order

```mermaid
sequenceDiagram
  actor User
  participant AM as AuthMiddleware
  participant CC as CatalogController
  participant CartJS as cartJS
  participant OC as OrderController
  participant OS as OrderService
  participant DB as MySQL
  User->>AM: GET /
  AM->>CC: list available products
  CC-->>User: cards + latest order
  User->>CartJS: add plus minus
  Note over CartJS: sessionStorage preview only
  User->>OC: POST /orders CSRF
  OC->>OS: place PlaceOrderRequest
  OS->>DB: begin
  OS->>DB: insert order PROCESSING
  OS->>DB: insert snapshot lines
  OS->>DB: commit
  OC-->>User: redirect home ordered
```

---

## 15. Sequence — history, detail, cancel

```mermaid
sequenceDiagram
  actor User
  participant OC as OrderController
  participant UQ as UserOrderQueryService
  participant OSS as OrderStatusService
  participant Pol as OrderPolicy
  participant DB as MySQL
  User->>OC: GET /orders?from&to
  OC->>UQ: getUserWithOrders self
  UQ->>DB: paginateForUser
  User->>OC: POST /orders/id/cancel
  OC->>OSS: cancel
  OSS->>Pol: canCancelOrder
  OSS->>DB: cancelIfProcessing
```

---

## 16. Sequence — fulfillment queue

```mermaid
sequenceDiagram
  actor Admin
  participant FC as FulfillmentController
  participant OSS as OrderStatusService
  participant MX as OrderTransitionMatrix
  participant DB as MySQL
  Admin->>FC: GET /admin/orders/current
  FC->>DB: listCurrentQueue
  Admin->>FC: POST status next
  FC->>OSS: transition
  OSS->>MX: canTransition
  OSS->>DB: transitionIfCurrent + history
```

---

## 17. Sequence — admin on-behalf

```mermaid
sequenceDiagram
  actor Admin
  participant AOC as AdminOrderController
  participant OS as OrderService
  participant DB as MySQL
  Admin->>AOC: POST /admin/orders
  AOC->>OS: placeOnBehalf
  OS->>DB: user_id = customer
  OS->>DB: created_by_user_id = admin
```

---

## 18. Sequence — CRUD, uploads, media

```mermaid
sequenceDiagram
  actor Admin
  participant PC as ProductController
  participant PS as ProductService
  participant SU as SafeUploader
  participant Disk as storage/uploads/products
  participant MC as MediaController
  participant Browser as img tag
  Admin->>PC: POST multipart image
  PC->>PS: create/update
  PS->>SU: hashed filename
  SU->>Disk: write
  PS->>DB: image_path storage/uploads/products/hex.ext
  Browser->>MC: GET /media/products/hex.ext
  MC->>Disk: realpath jail
  MC-->>Browser: image bytes
```

`PublicFileUrl` maps stored paths to `/media/...` or keeps `/assets/...` seed art. Admin **users** page lists both `USER` and `ADMIN` because there is no public signup.

---

## 19. Sequence — checks, drill-down, export

```mermaid
sequenceDiagram
  actor Admin
  participant RC as ReportController
  participant RQS as ReportQueryService
  participant RES as ReportExportService
  participant DB as MySQL
  Admin->>RC: GET /admin/checks
  RC->>RQS: summarize
  Admin->>RC: GET /admin/checks/users/id
  RC->>RQS: drillDown
  Admin->>RC: GET /admin/checks/export
  RC->>RES: export
  RES->>RQS: summarize same filter
```

---

## 20. Sequence — authorization boundaries

```mermaid
sequenceDiagram
  actor Guest
  actor User
  actor Admin
  participant Auth as AuthMiddleware
  participant Adm as AdminMiddleware
  Guest->>Auth: GET /admin/checks
  Auth-->>Guest: 302 /login
  User->>Adm: GET /admin/checks
  Adm-->>User: 403
  Admin->>Adm: GET /admin/checks
  Adm-->>Admin: 200 HTML
```

Hiding the navbar link is **not** the control. Middleware is.

---

## 21. Trust boundaries

```mermaid
flowchart TB
  subgraph publicNet [Untrusted]
    Browser
  end
  subgraph publicDir [Document root]
    Index[public/index.php]
    Assets[public/assets]
  end
  subgraph privateCode [Not web-served]
    App[app/]
    Boot[bootstrap/]
    Env[.env]
    Storage[storage/]
  end
  subgraph dbTrust [Database]
    MySQL
  end
  Browser --> Index
  Browser --> Assets
  Index --> App
  App --> Storage
  App --> MySQL
```

| Boundary | Control |
|----------|---------|
| Browser → app | CSRF on POST, escaped HTML, session cookie HttpOnly/SameSite |
| Query → SQL | PDO bound parameters; scalar report filters |
| Upload → disk | MIME + content check, generated names, not under `public/` |
| Media URL → disk | Hex filename regex + `realpath` prefix |
| USER → ADMIN data | `AdminMiddleware` + policies |

---

## 22. Route atlas

Registered from [routes/web.php](../routes/web.php) (`/media`, `/health` in bootstrap), [routes/auth.php](../routes/auth.php), [routes/orders.php](../routes/orders.php), [routes/admin.php](../routes/admin.php). Optional `reports.php` is not present; checks live in `admin.php`.

### 22.1 Public / guest

| Method | Path | Middleware |
|--------|------|------------|
| GET | `/health` | none |
| GET | `/media/{kind}/{filename}` | none |
| GET/POST | `/login` | Guest |
| GET/POST | `/forgot-password` | Guest |
| GET/POST | `/reset-password` | Guest |

### 22.2 Authenticated user

| Method | Path | Notes |
|--------|------|-------|
| POST | `/logout` | CSRF |
| GET | `/` | Catalogue |
| GET | `/orders/new` | Cart form |
| POST | `/orders` | Place |
| GET | `/orders` | History |
| GET | `/orders/{id}` | Detail |
| POST | `/orders/{id}/cancel` | CSRF |

### 22.3 Admin

| Method | Path | Notes |
|--------|------|-------|
| CRUD | `/admin/users`, categories, products | List includes both roles for users |
| GET | `/admin/orders`, `/admin/orders/current` | Queue |
| GET/POST | `/admin/orders/create`, `/admin/orders` | On-behalf |
| POST | `/admin/orders/{id}/status` | Fulfillment |
| GET | `/admin/checks` | Summary |
| GET | `/admin/checks/users/{id}` | Drill-down |
| GET | `/admin/checks/export` | CSV |

---

## 23. Data model

Canonical picture: [docs/diagrams/erd.svg](diagrams/erd.svg).

```mermaid
erDiagram
  ROOMS ||--o{ USERS : has
  USERS ||--o{ ORDERS : places
  USERS ||--o{ ORDERS : creates
  CATEGORIES ||--o{ PRODUCTS : contains
  ORDERS ||--|{ ORDER_ITEMS : lines
  ORDERS ||--o{ ORDER_STATUS_HISTORY : audit
  USERS ||--o{ PASSWORD_RESET_TOKENS : reset
  ROOMS {
    int id
    string name
  }
  USERS {
    int id
    string email
    string role
    tinyint is_active
  }
  PRODUCTS {
    int id
    string name
    decimal price
    string image_path
    datetime deleted_at
  }
  ORDERS {
    int id
    string status
    decimal total_amount
    datetime cancelled_at
  }
  ORDER_ITEMS {
    int id
    string product_name_snapshot
    decimal unit_price_snapshot
    int quantity
  }
  ORDER_STATUS_HISTORY {
    int id
    string from_status
    string to_status
  }
```

Seeds: 3 rooms, 3 categories, 4 products, 2 users (`docs/database/seeding.md`).

---

## 24. Class inventory

### 24.1 Repository contracts

`AuthUserRepositoryInterface`, `AdminUserRepositoryInterface`, `PasswordResetTokenRepositoryInterface`, `CategoryRepositoryInterface`, `ProductRepositoryInterface`, `OrderCommandRepositoryInterface`, `OrderQueryRepositoryInterface`, `ReportRepositoryInterface`.

### 24.2 PDO implementations

Matching `app/Repositories/Pdo/Pdo*.php` for each contract.

### 24.3 DTOs

`LoginRequest`, `ForgotPasswordRequest`, `ResetPasswordRequest`, `CreateUserRequest`, `UpdateUserRequest`, `CreateCategoryRequest`, `UpdateCategoryRequest`, `CreateProductRequest`, `UpdateProductRequest`, `PlaceOrderRequest`, `OrderItemInput`, `PlaceOrderOnBehalfRequest`, `OrderHistoryFilter`, `ChecksFilter`.

---

## 25. Diagram index

| Diagram | Section | Type |
|---------|---------|------|
| System context | §2 | C4 / flowchart |
| Containers | §3 | Architecture |
| HTTP components | §4 | Architecture |
| Code clusters | §5 | Architecture |
| Layer dependencies | §6 | Architecture |
| Composition root | §7 | Architecture |
| DFD L0 | §8 | DFD |
| DFD L1 | §9 | DFD |
| Place-order DFD L2 | §10.1 | DFD |
| Export DFD L2 | §10.2 | DFD |
| Order state | §11.1 | State / queue |
| Queue set | §11.2 | Queue |
| HTTP pipeline | §12 | Sequence |
| Login | §13 | Sequence |
| Place order | §14 | Sequence |
| History/cancel | §15 | Sequence |
| Fulfillment | §16 | Sequence |
| On-behalf | §17 | Sequence |
| Upload/media | §18 | Sequence |
| Checks | §19 | Sequence |
| Authz | §20 | Sequence |
| Trust | §21 | Architecture |
| ER | §23 | Data |

---

## Appendix A — Day guides

| Day | Phase | Guide |
|-----|-------|--------|
| 1 | P0 | [day-1-foundation-guide.md](day-1-foundation-guide.md) |
| 2 | P1 | [day-2-authentication-admin-guide.md](day-2-authentication-admin-guide.md) |
| 3 | P2 | [day-3-catalog-ordering-guide.md](day-3-catalog-ordering-guide.md) |
| 4 | P3 | [day-4-order-lifecycle-guide.md](day-4-order-lifecycle-guide.md) |
| 5 | P4 | [day-5-reporting-security-guide.md](day-5-reporting-security-guide.md) |

## Appendix B — ADRs

| ADR | Topic |
|-----|--------|
| 0001 | Modular monolith MVC |
| 0002 | Public document root |
| 0003 | Session / CSRF |
| 0004 | Price snapshots |
| 0005 | Order state machine |
| 0006 | Reporting security |

## Appendix C — Export to PDF

```bash
pandoc docs/system-through-day-5-architecture-guide.md -o docs/system-through-day-5-architecture-guide.pdf --toc -V geometry:margin=1in
```

GitHub and Cursor Markdown Preview render standard Mermaid (`flowchart`, `sequenceDiagram`, `stateDiagram-v2`, `erDiagram`). C4 plugin syntax is not used.

---

*End of Architecture Atlas (through Day 5)*
