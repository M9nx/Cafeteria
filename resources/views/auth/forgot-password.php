<?php

declare(strict_types=1);

/** @var string|null $email */
/** @var list<string>|array<string, string>|null $errors */
/** @var string|null $csrfField */
/** @var string|null $resultMessage Generic success/info message after submit */

$emailValue = htmlspecialchars((string) ($email ?? ''), ENT_QUOTES, 'UTF-8');
$resultMessage = trim((string) ($resultMessage ?? ''));
?>
<section aria-labelledby="forgot-password-heading">
    <h1 id="forgot-password-heading" class="h3 mb-3 text-center">Forgot password</h1>
    <p class="text-muted text-center mb-4">
        Enter your email address and we will send reset instructions if the account exists.
    </p>

    <?php if ($resultMessage !== ''): ?>
        <div class="alert alert-info" role="status">
            <?= htmlspecialchars($resultMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php
    $errors = $errors ?? null;
    require dirname(__DIR__) . '/components/form-errors.php';
    ?>

    <form action="/forgot-password" method="post" class="app-form" novalidate>
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

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Send reset link</button>
        </div>
    </form>

    <p class="text-center mt-4 mb-0">
        <a href="/login">Back to login</a>
    </p>
</section>
