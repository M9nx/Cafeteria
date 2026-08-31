<?php

declare(strict_types=1);

/** @var string|null $token */
/** @var list<string>|array<string, string>|null $errors */
/** @var string|null $csrfField */

$tokenValue = htmlspecialchars((string) ($token ?? ''), ENT_QUOTES, 'UTF-8');
?>
<section aria-labelledby="reset-password-heading">
    <h1 id="reset-password-heading" class="h3 mb-3 text-center">Reset password</h1>
    <p class="text-muted text-center mb-4">Choose a new password for your account.</p>

    <?php
    $errors = $errors ?? null;
    require dirname(__DIR__) . '/components/form-errors.php';
    ?>

    <form action="/reset-password" method="post" class="app-form" novalidate>
        <?php if (isset($csrfField) && is_string($csrfField)): ?>
            <?= $csrfField ?>
        <?php else: ?>
            <!-- CSRF token slot for future auth wiring -->
            <input type="hidden" name="_csrf" value="">
        <?php endif; ?>

        <input type="hidden" name="token" value="<?= $tokenValue ?>">

        <div class="mb-3">
            <label for="password" class="form-label">New password</label>
            <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                autocomplete="new-password"
                required
                aria-describedby="password-error"
            >
            <?php
            $fieldId = 'password';
            $message = is_array($errors ?? null) && !array_is_list($errors)
                ? ($errors['password'] ?? null)
                : null;
            require dirname(__DIR__) . '/components/field-error.php';
            ?>
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm new password</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                class="form-control"
                autocomplete="new-password"
                required
                aria-describedby="password_confirmation-error"
            >
            <?php
            $fieldId = 'password_confirmation';
            $message = is_array($errors ?? null) && !array_is_list($errors)
                ? ($errors['password_confirmation'] ?? null)
                : null;
            require dirname(__DIR__) . '/components/field-error.php';
            ?>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Reset password</button>
        </div>
    </form>

    <p class="text-center mt-4 mb-0">
        <a href="/login">Back to login</a>
    </p>
</section>
