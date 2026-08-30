# ADR 0001: Modular Monolith with MVC and Service/Repository Layers

## Status

Accepted — Day 1 (P0-LEAD)

## Context

The cafeteria project must be delivered in six working days by five team members with mixed experience levels. The course brief requires PHP, MySQL, HTML/CSS/JavaScript, validation, security, and GitHub workflow evidence — but does not require a specific framework.

We need an architecture that:

- Teaches separation of concerns
- Allows parallel work on independent vertical features
- Remains deployable as a single application and database
- Supports testing without heavy infrastructure

## Decision

Build **one deployable modular monolith** in plain PHP 8.4.x with:

- MVC for HTTP/UI organization
- A Service Layer for business use cases
- Repository interfaces with PDO implementations
- Request/DTO objects and validators at input boundaries
- Policies for authorization

**No full-stack framework and no microservices** for this six-day project.

## Consequences

### Positive

- Low ceremony; team owns every layer
- Clear module ownership for parallel PRs
- Single deploy artifact matches course scope
- Repository interfaces enable unit testing with fakes

### Negative

- No framework-provided routing, ORM, or form helpers — we implement core infrastructure in P0–P1
- Discipline required to keep controllers thin and logic out of views

## References

- Master issue #1 — architecture section
- `docs/architecture.md` — layer map and request flow
