# View Layer Contract

Presentation templates live under `resources/views/`. They receive prepared data from controllers/services and must not access repositories, run SQL, or enforce authorization rules.

## Layouts

### `layouts/app.php` (authenticated shell)

| Variable | Type | Required | Purpose |
|----------|------|----------|---------|
| `$title` | `string` | yes | Page `<title>` and heading context |
| `$content` | `string` | yes | Rendered inner view HTML |
| `$currentUser` | `object|null` | no | User object with `isAdmin(): bool` for navbar presentation |
| `$flash` | `array{type?:string,message?:string}|null` | no | One flash message for `components/alerts.php` |
| `$csrfField` | `string|null` | no | Pre-rendered hidden CSRF input HTML for logout form |

### `layouts/guest.php` (login / reset shell)

| Variable | Type | Required | Purpose |
|----------|------|----------|---------|
| `$title` | `string` | yes | Page title |
| `$content` | `string` | yes | Inner auth view HTML |
| `$flash` | `array{type?:string,message?:string}|null` | no | Optional flash message |

Guest layout intentionally excludes authenticated navigation.

## Components

### `components/navbar.php`

Presentation-only navigation. Authorization must already be resolved before rendering.

- Show admin links only when `$currentUser->isAdmin()` is true.
- Never compute roles from raw session data inside the view.

### `components/alerts.php`

| Variable | Type | Purpose |
|----------|------|---------|
| `$flash` | `array{type?:string,message?:string}` | Escaped Bootstrap alert |

Allowed types: `success`, `danger`, `warning`, `info`.

All dynamic text uses:

```php
htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
```

### `components/form-errors.php`

| Variable | Type | Purpose |
|----------|------|---------|
| `$errors` | `list<string>` or `array<string,string>` | Validation summary |

Renders `role="alert"` with a list of escaped errors. Associative errors link to field IDs for keyboard navigation.

### `components/field-error.php`

| Variable | Type | Purpose |
|----------|------|---------|
| `$fieldId` | `string` | Base field ID (`email` → `email-error`) |
| `$message` | `string` | Escaped field-level message |

Use with `aria-describedby="<?= $fieldId ?>-error"` on the related input.

## Auth skeletons

| View | Form action | Notes |
|------|-------------|-------|
| `auth/login.php` | `POST /login` | Repopulates email only; never repopulates password |
| `auth/forgot-password.php` | `POST /forgot-password` | Email-only request; generic `$resultMessage` area |
| `auth/reset-password.php` | `POST /reset-password` | Hidden `token`, password + confirmation fields |

Shared optional variables:

- `$errors` — validation errors (summary + field level)
- `$csrfField` — pre-rendered CSRF hidden input slot

## Escaping rules

1. Escape all dynamic output with `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
2. Views must not call repositories or services.
3. JavaScript in `public/assets/js/app.js` is presentation-only (dismiss alerts, focus helpers).
4. Do not remove visible `:focus-visible` indicators (see `public/assets/css/app.css`).

## Expected states

| State | View behavior |
|-------|---------------|
| Empty | Forms render with labels and empty inputs |
| Error | `$errors` populates summary + field messages |
| Success | `$flash` or `$resultMessage` shows escaped info/success text |

## Rendering example

```php
use Cafeteria\Core\View\View;

return View::render('auth/login', [
    'title' => 'Log in',
    'email' => $oldEmail,
    'errors' => ['email' => 'Invalid credentials.'],
], 'layouts/guest');
```

The layout receives `$content` automatically from the view renderer.

## Catalogue and Order Views

Controllers render `user.catalog.index` and `user.orders.create`.
Both views include `components/catalog-assets.php` for stylesheet and cart script loading.

### `user/catalog/index.php`

Receives prepared catalogue data from the controller.

| Variable | Type | Required | Purpose |
|----------|------|----------|---------|
| `$products` | `array{items:list<array<string,mixed>>,total:int,page:int,per_page:int}` | yes | Available products and pagination metadata |
| `$latestOrder` | `array<string,mixed>|null` | no | Latest order preview for the authenticated user |

The view renders available products through `components/product-card.php`.

### `components/product-card.php`

Receives one prepared product:

| Variable | Type | Required | Purpose |
|----------|------|----------|---------|
| `$product` | `array<string,mixed>` | yes | Product data prepared by the controller |

The component displays the product name, category, price, optional image, and an accessible "Add to cart" action.

### `components/cart-summary.php`

Receives client-side cart presentation data:

| Variable | Type | Required | Purpose |
|----------|------|----------|---------|
| `$cartItems` | `array<int,array<string,mixed>>` | no | Current cart items for presentation |

The cart summary displays selected items and a client-side total preview.

### `user/orders/create.php`

Receives prepared order-form data:

| Variable | Type | Required | Purpose |
|----------|------|----------|---------|
| `$products` | `array{items:list<array<string,mixed>>,total:int,page:int,per_page:int}` | yes | Available products |
| `$rooms` | `list<array{id:int,name:string}>` | yes | Active delivery rooms |
| `$errors` | `list<string>` | no | Validation errors |
| `$old` | `array<string,mixed>` | no | Previously submitted non-sensitive values |

The form submits to `POST /orders` using `room_id`, `notes`, and `items[][product_id]` / `items[][quantity]`.

The browser-side total is only a preview. Server-side validation and pricing remain authoritative.

## Catalogue Assets

### `public/assets/js/cart.js`

Presentation-only cart behavior:

- Add available products to the cart.
- Increase and decrease quantities.
- Remove an item when its quantity reaches zero.
- Update line totals and the displayed order total.
- Generate hidden order item fields for form submission.
- Do not perform authorization, repository access, or server-side pricing decisions.

### `public/assets/css/catalog.css`

Contains responsive presentation styles for catalogue product cards, cart summary, and order-form layout.

### Client-side authority

Client-side cart state and totals are previews only. The server must validate product availability, quantities, room selection, and authoritative prices when the order is submitted.
Order Lifecycle Views

"user/orders/index.php"

Receives prepared authenticated order-history data.

Variable| Type| Required| Purpose
"$orders"| "array{items:list<array<string,mixed>>,total:int,page:int,per_page:int}"| yes| Authenticated user's paginated orders
"$filters"| "array<string,mixed>"| no| Current date-filter values
"$canCancel"| "bool"| no| Presentation hint for showing eligible cancel actions
"$csrfField"| `string| null`| no
"$flashMessages"| `array<string,string>| null`| no

The view renders a date-filter form and paginated order history. Dynamic status and total values must be escaped.

Cancel actions are shown only for orders owned by the authenticated user whose status is "PROCESSING". Authorization remains server-side.

"user/orders/show.php"

Receives one prepared order owned by the authenticated user.

Variable| Type| Required| Purpose
"$order"| "array<string,mixed>"| yes| Prepared order detail including line items and notes
"$canCancel"| "bool"| no| Presentation hint for the cancel action
"$flashMessages"| `array<string,string>| null`| no
"$csrfField"| `string| null`| no

The view renders order metadata, notes, and line items through "components/order-detail-panel.php".

Order links and actions must be ownership-safe. Raw IDs from query strings must not be treated as authorization evidence.

"admin/orders/queue.php"

Receives the prepared current-order queue.

Variable| Type| Required| Purpose
"$queue"| "array<string,mixed>"| yes| Current orders and queue metadata
"$csrfField"| `string| null`| no
"$flashMessages"| `array<string,string>| null`| no

The view renders current orders, status badges, and allowed status-transition POST controls. Empty queues render an accessible empty state.

Status transitions must use POST with CSRF protection. Authorization and transition validity remain server-side.

"components/order-status-badge.php"

Variable| Type| Required| Purpose
"$status"| "string"| yes| Prepared order status

The component maps "PROCESSING", "OUT_FOR_DELIVERY", "DONE", and "CANCELLED" to escaped Bootstrap badge presentation.

"components/order-detail-panel.php"

Variable| Type| Required| Purpose
"$items"| "array<int,array<string,mixed>>"| yes| Prepared order line items

The component displays escaped product names, quantities, and line totals and provides an empty state when no items exist.

Order lifecycle client assets

"public/assets/js/order-details.js"

Presentation-only progressive enhancement for expandable order-history rows/details.

The script may toggle visibility and accessibility attributes such as "hidden" and "aria-expanded". It must not perform authorization checks, pricing decisions, or server-side state changes.

"public/assets/css/orders.css"

Responsive presentation styles for order history and queue views. Styles must remain compatible with the Bootstrap shell and preserve visible keyboard focus indicators.