# Requirements Traceability Matrix

This document maps the core cafeteria system requirements to unique
identifiers and their planned acceptance tests.

## Requirements

| ID | Area | Requirement | Acceptance Tests |
|---|---|---|---|
| AUTH-001 | Authentication | User, admin, and guest authentication is supported. | AT-047 |
| AUTH-002 | Authentication | Users can log in and log out. | AT-001, AT-002, AT-003 |
| AUTH-003 | Authentication | Users can request a password reset. | AT-004 |
| AUTHZ-001 | Authorization | Regular users cannot access admin-only pages. | AT-036 |
| AUTHZ-002 | Authorization | Users can access only their own order records unless they are admins. | AT-037 |
| AUTHZ-003 | Authorization | Admins can access another user's orders for authorized admin workflows. | AT-038 |
| AUTHZ-004 | Authorization | Only admins can advance order status. | AT-039 |
| ORD-001 | User ordering | Authenticated users can browse the product catalogue. | AT-005 |
| ORD-002 | User ordering | Users can add and remove products from the cart. | AT-006 |
| ORD-003 | User ordering | Users can add order notes and select a room. | AT-007 |
| ORD-004 | User ordering | The server calculates the order total. | AT-008 |
| ORD-005 | User ordering | Users can confirm an order and view their latest order. | AT-009 |
| ORD-006 | Admin ordering | Admins can create an order on behalf of a selected user. | AT-010 |
| ORD-007 | User ordering | Client-supplied totals cannot override server-calculated order totals. | AT-043 |
| ORD-008 | User ordering | Order items retain product name and price snapshots for historical accuracy. | AT-044 |
| HIST-001 | Order history | Users can view their own order history. | AT-011 |
| HIST-002 | Order history | Users can filter order history by date. | AT-012 |
| HIST-003 | Order history | Users can view order totals, status, and details. | AT-013 |
| HIST-004 | Order history | Users can cancel their own processing orders only. | AT-014, AT-015 |
| PROD-001 | Product management | Admins can create, read, update, and delete products. | AT-016, AT-017, AT-018 |
| PROD-002 | Product management | Product lists support pagination. | AT-019 |
| PROD-003 | Product management | Referenced products are soft deleted. | AT-020 |
| USER-001 | User management | Admins can create, read, update, and delete users. | AT-021, AT-022 |
| USER-002 | User management | Referenced users can be deactivated. | AT-023 |
| USER-003 | User management | Users can have a profile image. | AT-024 |
| CAT-001 | Categories | Admins can create, read, update, and delete categories. | AT-025, AT-026, AT-028 |
| CAT-002 | Categories | Category operations validate their input. | AT-027 |
| REP-001 | Reports | Admins can generate reports for a date range. | AT-029 |
| REP-002 | Reports | Reports support an optional user filter. | AT-030 |
| REP-003 | Reports | Reports provide aggregate totals. | AT-031 |
| REP-004 | Reports | Admins can drill down from report results to related order details. | AT-032 |
| REP-005 | Reports | Cancelled orders are excluded from default monetary report totals. | AT-045 |
| QUEUE-001 | Current-order queue | Admins can view active orders. | AT-033 |
| QUEUE-002 | Current-order queue | Admins can advance orders through controlled status transitions. | AT-034, AT-035 |
| SYS-001 | System | Order timestamps are stored in UTC and displayed in the configured local timezone. | AT-046 |
| UI-001 | Shared UI | Authenticated users receive a responsive Bootstrap layout. | AT-040 |
| UI-002 | Shared UI | Forms provide validation and flash messages where required. | AT-041, AT-042 |
| ENG-001 | Engineering | The system applies validation and security controls. | AT-048 |
| ENG-002 | Engineering | Automated tests cover relevant system behavior. | AT-049 |
| ENG-003 | Engineering | Project documentation is maintained. | AT-050 |
| ENG-004 | Engineering | The team follows the documented Git and PR workflow. | AT-051 |
