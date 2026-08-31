# Acceptance Test Matrix

This document defines acceptance tests for the core cafeteria system
requirements using testable Given-When-Then scenarios.

## Acceptance Tests

| Test ID | Requirement ID | Scenario | Expected Result |
|---|---|---|---|
| AT-001 | AUTH-002 | Given a registered user with valid credentials, when the user submits the login form, then the user is authenticated successfully. | User is logged in and redirected to the authenticated area. |
| AT-002 | AUTH-002 | Given a registered user with invalid credentials, when the user submits the login form, then authentication is rejected. | User remains unauthenticated and receives a validation/error message. |
| AT-003 | AUTH-002 | Given an authenticated user, when the user selects logout, then the session is terminated. | User is logged out and cannot access authenticated pages without logging in again. |
| AT-004 | AUTH-003 | Given a registered user, when the user requests a password reset, then the system accepts the reset request. | The reset process is started without exposing sensitive account information. |

| AT-005 | ORD-001 | Given an authenticated user, when the user opens the catalogue, then available products are displayed. | The user can view available products and their prices. |
| AT-006 | ORD-002 | Given an authenticated user with products available, when the user adds or removes a product from the cart, then the cart contents are updated. | The cart reflects the requested quantity changes. |
| AT-007 | ORD-003 | Given an authenticated user with an order, when the user enters order notes and selects a room, then the order stores the supplied information. | The order contains the submitted notes and room. |
| AT-008 | ORD-004 | Given a cart containing products, when the user submits the order, then the server calculates the total from the stored product prices. | The final order total matches the server-side calculation. |
| AT-009 | ORD-005 | Given an authenticated user with a valid cart, when the user confirms the order, then the order is created successfully. | The order is stored and becomes the user's latest order. |
| AT-010 | ORD-006 | Given an authenticated admin, when the admin selects a user and creates an order for that user, then the order is created for the selected user. | The created order belongs to the selected user. |

| AT-011 | HIST-001 | Given an authenticated user with previous orders, when the user opens order history, then only that user's orders are displayed. | The user can view their own orders and not another user's orders. |
| AT-012 | HIST-002 | Given an authenticated user with orders on different dates, when the user applies a date filter, then only orders matching the selected date range are displayed. | The results match the selected date range. |
| AT-013 | HIST-003 | Given an authenticated user with an existing order, when the user opens the order details, then the order total, status, and details are displayed. | The displayed information matches the stored order. |
| AT-014 | HIST-004 | Given an authenticated user with a PROCESSING order, when the user requests cancellation, then the order is cancelled. | The order status changes to CANCELLED. |
| AT-015 | HIST-004 | Given an authenticated user with a DONE or OUT_FOR_DELIVERY order, when the user requests cancellation, then cancellation is rejected. | The order status remains unchanged. |

| AT-016 | PROD-001 | Given an authenticated admin, when the admin creates a product with valid data, then the product is created successfully. | The new product appears in the product list. |
| AT-017 | PROD-001 | Given an authenticated admin with an existing product, when the admin updates the product with valid data, then the product is updated. | The product shows the updated information. |
| AT-018 | PROD-001 | Given an authenticated admin with an existing product, when the admin deletes the product, then the product is removed according to the product deletion rules. | The product is no longer available as an active product. |
| AT-019 | PROD-002 | Given an authenticated admin with more products than one page can display, when the admin opens the product list, then the products are divided into pages. | Pagination allows the admin to navigate between product pages. |
| AT-020 | PROD-003 | Given a product referenced by an existing order, when the admin deletes the product, then the product is soft deleted. | The product is not physically removed and remains available for historical references. |

| AT-021 | USER-001 | Given an authenticated admin, when the admin creates a user with valid data, then the user is created successfully. | The new user appears in the user list. |
| AT-022 | USER-001 | Given an authenticated admin with an existing user, when the admin updates the user with valid data, then the user is updated. | The user shows the updated information. |
| AT-023 | USER-002 | Given a user referenced by existing orders, when the admin deactivates that user, then the user becomes inactive without deleting historical orders. | The user is marked inactive and existing orders remain available. |
| AT-024 | USER-003 | Given an authenticated admin, when the admin uploads a valid profile image for a user, then the image is stored successfully. | The user's profile displays the uploaded image. |
| AT-025 | CAT-001 | Given an authenticated admin, when the admin creates a category with valid data, then the category is created successfully. | The new category appears in the category list. |
| AT-026 | CAT-001 | Given an authenticated admin, when the admin updates a category with valid data, then the category is updated. | The category shows the updated information. |
| AT-027 | CAT-002 | Given an authenticated admin, when the admin submits invalid category data, then validation rejects the request. | The category is not created or updated and validation errors are displayed. |
| AT-028 | CAT-001 | Given an authenticated admin with an existing category, when the admin attempts to delete the category, then the system applies the category deletion validation rules. | The category is deleted only when deletion is allowed; otherwise the request is rejected and data integrity is preserved. |

| AT-029 | REP-001 | Given an authenticated admin, when the admin selects a valid date range for a report, then orders within that range are included. | The report contains only orders matching the selected date range. |
| AT-030 | REP-002 | Given an authenticated admin, when the admin applies an optional user filter, then only matching orders are included. | The report results match the selected user. |
| AT-031 | REP-003 | Given an authenticated admin, when the admin opens the report, then the report displays the required aggregates. | The aggregates are calculated from the matching orders. |
| AT-032 | REP-004 | Given an authenticated admin, when the admin opens a report result, then the admin can view the related order details. | The drill-down displays the relevant order information. |
| AT-033 | QUEUE-001 | Given an authenticated admin with active orders, when the admin opens the current-order queue, then active orders are displayed. | The queue shows orders that require fulfillment action. |
| AT-034 | QUEUE-002 | Given an authenticated admin with an active order, when the admin changes its status using an allowed transition, then the status is updated. | The order moves to the requested valid status. |
| AT-035 | QUEUE-002 | Given an authenticated admin with an active order, when the admin attempts an invalid status transition, then the transition is rejected. | The order status remains unchanged. |

| AT-036 | AUTHZ-001 | Given a regular user, when the user attempts to access an admin-only page, then access is denied. | The user cannot access admin functionality. |
| AT-037 | AUTHZ-002 | Given a regular user, when the user attempts to view another user's order, then access is denied. | The user can view only their own orders. |
| AT-038 | AUTHZ-003 | Given an admin, when the admin accesses another user's orders, then access is allowed. | The admin can view the selected user's orders. |
| AT-039 | AUTHZ-004 | Given a regular user, when the user attempts to advance an order status, then the action is denied. | The order status is not changed. |

| AT-040 | UI-001 | Given an authenticated user on a supported screen size, when the user navigates through the application, then the shared layout adapts to the screen. | The interface remains usable on responsive screen sizes. |
| AT-041 | UI-002 | Given a user submits invalid form data, when the form is processed, then validation messages are displayed. | Invalid data is rejected and useful validation feedback is shown. |
| AT-042 | UI-002 | Given a completed action, when the user is redirected to the next page, then the appropriate flash message is displayed. | The user receives feedback about the action result. |
| AT-043 | ORD-007 | Given an order containing products, when the order total is calculated, then the server recalculates the total using trusted product prices. | Client-supplied totals cannot override the server-calculated total. |
| AT-044 | ORD-008 | Given an existing order item, when the original product name or price changes later, then the historical order item retains its stored snapshot. | Historical order data remains unchanged. |
| AT-045 | REP-005 | Given a cancelled order, when a monetary report is generated without explicitly including cancelled orders, then the cancelled order is excluded from monetary totals. | Cancelled orders do not affect the default monetary aggregates. |
| AT-046 | SYS-001 | Given an order timestamp stored by the system, when the timestamp is displayed to the user, then it is converted from UTC to the configured local time. | The displayed time matches the configured local timezone. |

| AT-047 | AUTH-001 | Given a guest, user, or admin, when the actor uses the authentication entry point according to their role, then the system applies the appropriate authentication behavior. | Access is granted or restricted according to the actor's authentication state and role. |
| AT-048 | ENG-001 | Given a system operation with invalid or unauthorized input, when the operation is submitted, then validation and security controls are applied. | Invalid or unauthorized requests are rejected safely. |
| AT-049 | ENG-002 | Given the project test suite, when the automated tests are executed, then the relevant tests run successfully. | Automated tests report their execution results without unexpected failures. |
| AT-050 | ENG-003 | Given a project change that affects documented behavior or architecture, when the change is completed, then the relevant documentation is updated. | Project documentation remains consistent with the implemented system. |
| AT-051 | ENG-004 | Given a team member implementing a project change, when the change is submitted, then the documented Git and PR workflow is followed. | The change uses the required branch, commit, PR, review, and merge process. |