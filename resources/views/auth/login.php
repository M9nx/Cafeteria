<?php

declare(strict_types=1);

/** @var string|null $email */
/** @var list<string>|array<string, string>|null $errors */
/** @var string|null $csrfField */

$emailValue = htmlspecialchars((string) ($email ?? ''), ENT_QUOTES, 'UTF-8');
?>
<section aria-labelledby="login-heading">
    <h1 id="login-heading" class="h3 mb-3 text-center">Log in</h1>
    <p class="text-muted text-center mb-4">Sign in with your cafeteria account.</p>

    <?php
    $errors = $errors ?? null;
    require dirname(__DIR__) . '/components/form-errors.php';
    ?>

    <form action="/login" method="post" class="app-form" novalidate>
        <?php if (isset($csrfField) && is_string($csrfField)): ?>
            <?= $csrfField ?>
        <?php else: ?>
            <!-- CSRF token slot for future auth wiring -->
            <input type="hidden" name="_csrf" value="">
        <?php endif; ?>

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                class="form-control"
                value="<?= $emailValue ?>"
                autocomplete="username"
                required
                aria-describedby="email-error"
            >
            <?php
            $fieldId = 'email';
            $message = is_array($errors ?? null) && !array_is_list($errors)
                ? ($errors['email'] ?? null)
                : null;
            require dirname(__DIR__) . '/components/field-error.php';
            ?>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                autocomplete="current-password"
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

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Log in</button>
        </div>
    </form>

    <p class="text-center mt-4 mb-0">
        <a href="/forgot-password">Forgot your password?</a>
    </p>
</section>
