# Project Scope — Cafeteria Management System

**Control reference:** [Master issue #1](https://github.com/M9nx/Cafeteria/issues/1)  
**Stable version boundary:** Six working days · five team members · 150 total hours

## Objective

Deliver a secure cafeteria ordering system where users create and track orders, while administrators manage users, products, categories, fulfillment, and date-based order reports.

## Required capabilities (in scope)

| Area | Actor | Summary |
|---|---|---|
| Authentication | Guest / User / Admin | Login, logout, password reset (email delivery is bonus) |
| User ordering | User | Catalogue, cart (+/−), notes, room, server-calculated total, confirm, latest order |
| Admin ordering | Admin | Create order on behalf of a selected user |
| Order history | User | Date filter, totals, status, details, cancel processing orders only |
| Product management | Admin | CRUD, pagination, soft delete when referenced |
| User management | Admin | CRUD, deactivation when referenced, profile image |
| Categories | Admin | CRUD with validation |
| Checks / reports | Admin | Date range, optional user filter, aggregates, drill-down |
| Current-order queue | Admin | Active orders, controlled status transitions |
| Shared UI | All authenticated | Responsive Bootstrap layout, validation, flash messages |
| Engineering | Team | Validation, security controls, tests, documentation, Git workflow |

## Explicit exclusions (out of scope)

Unless formally approved via change request:

- Online card payment or payment gateway
- Inventory / stock purchasing
- Delivery routing or multi-cafeteria tenancy
- Native mobile application
- Public REST API or microservices
- Real-time WebSockets
- Coupons, taxation, refunds, accounting integration
- Room CRUD (rooms are seeded reference data)
- Admin room management beyond selector usage

## Bonus items (conditional)

- SMTP email delivery for password reset
- CSV export on reports
- KPI dashboard or dark mode (Day 6 gated slot)

## Roles and authorization

| Capability | Guest | User | Admin |
|---|:---:|:---:|:---:|
| Login / reset | Yes | Yes | Yes |
| Browse products (after login) | No | Yes | Yes |
| Create own order | No | Yes | Yes |
| Create order for another user | No | No | Yes |
| View own orders | No | Yes | Yes |
| View another user's orders | No | No | Yes |
| Cancel own processing order | No | Yes | Yes |
| Advance order status | No | No | Yes |
| Manage products / categories / users | No | No | Yes |
| View reports | No | No | Yes |

## Business rules (canonical)

1. Roles: `USER` and `ADMIN`.
2. Order statuses: `PROCESSING`, `OUT_FOR_DELIVERY`, `DONE`, `CANCELLED`.
3. Only `PROCESSING` orders may be cancelled.
4. Users may view/cancel only their own orders.
5. Admin may create orders for a selected user.
6. Products belong to categories with explicit decimal prices.
7. Server recalculates all totals; client totals are never trusted.
8. Order items store product name/price snapshots.
9. Cancelled orders excluded from monetary report totals unless explicitly filtered.
10. Currency: EGP; timestamps stored UTC, displayed in configured local time.

## Six-day delivery boundary

This document defines the **stable version** delivered by Day 6. Day 1 (P0) establishes foundation and governance only; feature screens follow in P1–P5 per the workbook and phase issues.
