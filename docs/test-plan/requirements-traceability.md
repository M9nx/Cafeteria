# Requirements Traceability Matrix

This document maps the core cafeteria system requirements to unique
identifiers and their planned acceptance tests.

## Requirements

| ID | Area | Requirement |
|---|---|---|
| AUTH-001 | Authentication | User, admin, and guest authentication is supported. |
| AUTH-002 | Authentication | Users can log in and log out. |
| AUTH-003 | Authentication | Users can request a password reset. |
| ORD-001 | User ordering | Authenticated users can browse the product catalogue. |
| ORD-002 | User ordering | Users can add and remove products from the cart. |
| ORD-003 | User ordering | Users can add order notes and select a room. |
| ORD-004 | User ordering | The server calculates the order total. |
| ORD-005 | User ordering | Users can confirm an order and view their latest order. |
| ORD-006 | Admin ordering | Admins can create an order on behalf of a selected user. |
| HIST-001 | Order history | Users can view their own order history. |
| HIST-002 | Order history | Users can filter order history by date. |
| HIST-003 | Order history | Users can view order totals, status, and details. |
| HIST-004 | Order history | Users can cancel their own processing orders only. |
| PROD-001 | Product management | Admins can create, read, update, and delete products. |
| PROD-002 | Product management | Product lists support pagination. |
| PROD-003 | Product management | Referenced products are soft deleted. |
| USER-001 | User management | Admins can create, read, update, and delete users. |
| USER-002 | User management | Referenced users can be deactivated. |
| USER-003 | User management | Users can have a profile image. |
| CAT-001 | Categories | Admins can create, read, update, and delete categories. |
| CAT-002 | Categories | Category operations validate their input. |
| REP-001 | Reports | Admins can generate reports for a date range. |
| REP-002 | Reports | Reports support an optional user filter. |
| REP-003 | Reports | Reports provide aggregates and drill-down details. |
| QUEUE-001 | Current-order queue | Admins can view active orders. |
| QUEUE-002 | Current-order queue | Admins can advance orders through controlled status transitions. |
| UI-001 | Shared UI | Authenticated users receive a responsive Bootstrap layout. |
| UI-002 | Shared UI | Forms provide validation and flash messages where required. |
| ENG-001 | Engineering | The system applies validation and security controls. |
| ENG-002 | Engineering | Automated tests cover relevant system behavior. |
| ENG-003 | Engineering | Project documentation is maintained. |
| ENG-004 | Engineering | The team follows the documented Git and PR workflow. |