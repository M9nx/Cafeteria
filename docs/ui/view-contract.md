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
