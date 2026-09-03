# Manual UI Checklist — Catalogue, Order Flow, and Admin Order-on-Behalf

## Catalogue

- [ ] Available products are displayed as responsive Bootstrap cards.
- [ ] Each product card shows the product name and price.
- [ ] Product category is shown when available.
- [ ] Product image is shown when an image path is available.
- [ ] Clicking "Add to cart" adds the product with quantity 1.
- [ ] Adding the same product again increases its quantity.

## Cart

- [ ] Cart starts empty.
- [ ] Increasing quantity updates the displayed quantity.
- [ ] Decreasing quantity updates the displayed quantity.
- [ ] Decreasing quantity from 1 removes the item.
- [ ] Each cart item shows its line total.
- [ ] The displayed order total updates after every cart change.
- [ ] Cart remains usable on mobile screen widths.

## User order form

- [ ] Delivery room is required.
- [ ] Notes are optional.
- [ ] Selected cart items are submitted with product IDs and quantities.
- [ ] The order total shown in the browser is clearly a preview.
- [ ] The form can be submitted using keyboard navigation.
- [ ] Form controls have visible labels and accessible names.

## Admin order-on-behalf

- [ ] Admin can open `/admin/orders/create`.
- [ ] The admin-on-behalf page is accessible only to authenticated admins.
- [ ] Active customers are available in the customer selector.
- [ ] Inactive users are not available in the customer selector.
- [ ] Admin can select a customer before creating the order.
- [ ] Delivery room is required.
- [ ] Only active rooms are available for selection.
- [ ] Admin can add products to the shared cart.
- [ ] Admin can increase and decrease cart quantities.
- [ ] Admin can remove cart items.
- [ ] The displayed total is treated as a client-side preview only.
- [ ] Admin can submit the order with a valid customer, room, and cart.
- [ ] Successful admin-on-behalf creation redirects to the admin orders page.
- [ ] The created order identifies the selected customer as `user_id`.
- [ ] The created order identifies the logged-in admin as `created_by_user_id`.
- [ ] The order starts with `PROCESSING`.
- [ ] The order items are persisted in the same transaction as the order.
- [ ] Client-submitted total values cannot override the server-calculated total.
- [ ] A missing customer is rejected server-side.
- [ ] An inactive customer is rejected server-side.
- [ ] A non-user/admin account cannot be selected as the customer.
- [ ] A missing or inactive room is rejected server-side.
- [ ] An unavailable product is rejected server-side.
- [ ] Invalid or zero quantities are rejected server-side.
- [ ] Empty carts are rejected server-side.

## Validation and security

- [ ] Server-side validation remains authoritative for users, products, quantities, rooms, and prices.
- [ ] Dynamic product, customer, room, and form values are escaped in rendered HTML.
- [ ] No repository or SQL access is performed by the views.
- [ ] CSRF protection is included in the normal user order form.
- [ ] CSRF protection is included in the admin-on-behalf order form.
- [ ] A missing or invalid CSRF token blocks the admin order mutation.
- [ ] A normal authenticated user cannot access `/admin/orders/create`.
- [ ] A normal authenticated user cannot submit `POST /admin/orders`.
- [ ] Admin-only routes remain protected by `AdminMiddleware`.

## Regression checks — Day 4

- [ ] Admin user list still loads correctly.
- [ ] Admin user creation still works.
- [ ] Admin user update still works.
- [ ] Admin user deactivation still works.
- [ ] Admin category list still loads correctly.
- [ ] Admin category creation still works.
- [ ] Admin category update still works.
- [ ] Admin category deactivation still works.
- [ ] Admin product list still loads correctly.
- [ ] Admin product creation still works.
- [ ] Admin product update still works.
- [ ] Admin product deactivation still works.
- [ ] Admin current order queue still loads correctly.
- [ ] Admin order status transitions still work.
- [ ] Normal user catalogue still loads correctly.
- [ ] Normal user order creation still works.
- [ ] Normal user order history still loads correctly.
- [ ] Normal user order details still load correctly.
- [ ] Normal user cancellation rules still work.
- [ ] Existing CSRF protection continues to block invalid mutations.

## Critical admin-on-behalf case

- [ ] Selected customer is recorded as the order owner (`user_id`).
- [ ] Logged-in admin is recorded as the order creator (`created_by_user_id`).
- [ ] Customer and creator are not accidentally stored as the same user.
- [ ] Admin-on-behalf order uses the same server-side pricing path as normal orders.

## Responsive checks

- [ ] Catalogue cards remain readable on mobile.
- [ ] Cart/order summary does not obstruct the form on mobile.
- [ ] Customer selector remains usable on small screens.
- [ ] Room selector remains usable on small screens.
- [ ] Buttons remain reachable and usable on small screens.
- [ ] Keyboard focus remains visible.
Day 4 — Order Lifecycle UI Verification

- [ ] User order history loads correctly for the authenticated user.
- [ ] Date filter form displays and filters order history correctly.
- [ ] Order history pagination controls work correctly.
- [ ] Order status and totals are displayed with escaped values.
- [ ] Cancel button is visible only for owned "PROCESSING" orders.
- [ ] Cancel action uses POST and includes CSRF protection.
- [ ] Order detail page displays order metadata, notes, and line items.
- [ ] Order detail links and actions are ownership-safe.
- [ ] Empty order history displays a clear empty state.
- [ ] Empty order details display a clear line-item empty state.
- [ ] Admin current-order queue displays active orders.
- [ ] Queue status values are rendered using status badges.qq
- [ ] Queue displays a clear empty state when there are no active orders.
- [ ] Valid status transition buttons are displayed for each eligible order.
- [ ] Status transition forms use POST and include CSRF protection.
- [ ] Invalid status transitions are not offered by the UI.
- [ ] Flash messages and validation errors are displayed when provided.
- [ ] Expandable order-history details work without JavaScript-dependent authorization.
- [ ] Mobile order history remains usable without horizontal content loss.
- [ ] Mobile order queue remains usable at small screen widths.
- [ ] Visible keyboard focus indicators remain present on order controls.
## Day 5 — Reporting UI Verification

- [ ] Admin checks/report summary loads correctly.
- [ ] Report filters support `user_id`, `from`, `to`, and `include_cancelled`.
- [ ] Applying filters preserves the selected filter values in the form.
- [ ] Validation/filter errors are displayed as an accessible error summary.
- [ ] Report rows display escaped user names, order counts, and total amounts.
- [ ] Server-provided report totals are displayed correctly.
- [ ] Each report row provides an accessible drill-down action.
- [ ] User drill-down displays the selected user's prepared details.
- [ ] User drill-down displays matching orders correctly.
- [ ] User drill-down preserves the active date/cancelled filters when returning to the summary.
- [ ] Empty report results display a clear accessible empty state.
- [ ] Empty user drill-down results display a clear accessible empty state.
- [ ] Reporting tables remain usable on narrow/mobile screen widths.
- [ ] Wide reporting tables can be horizontally scrolled without losing content.
- [ ] Table headers use accessible scope/caption information.
- [ ] Keyboard users can reach and activate reporting controls.
- [ ] Visible keyboard focus indicators remain present.
- [ ] Client-side search/sort only changes already-rendered presentation rows.
- [ ] Client-side search/sort does not calculate authoritative totals.
- [ ] Client-side search/sort does not perform authorization checks.
- [ ] Reporting views perform no repository, SQL, or service access.
- [ ] Dynamic reporting values are escaped in rendered HTML.
- [ ] Existing admin order and previous regression checks still pass.