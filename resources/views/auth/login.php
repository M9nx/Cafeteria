<?php

declare(strict_types=1);

/** @var string|null $email */
/** @var list<string>|array<string, string>|null $errors */
/** @var string|null $csrfField */

$emailValue = htmlspecialchars((string) ($email ?? ''), ENT_QUOTES, 'UTF-8');
?>

<section class="login-section" aria-labelledby="login-heading">

    <div class="login-brand">
        <div class="login-brand-name">Fondo2na</div>
        <div class="login-brand-line"></div>
    </div>

    <div class="login-intro">
        <h1 id="login-heading">Welcome</h1>
        <p>Sign in to your account to continue.</p>
    </div>

    <?php
    $errors = $errors ?? null;
    require dirname(__DIR__) . '/components/form-errors.php';
    ?>

    <?php
$errors = $errors ?? null;
require dirname(__DIR__) . '/components/form-errors.php';
?>

<div class="login-start" id="login-start">
    <button type="button" class="login-submit" id="show-login-form">
        Log in
    </button>
</div>

<form action="/login" method="post" class="app-form login-form d-none" id="login-form" novalidate>

    <input
        type="hidden"
        name="_csrf_token"
        value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>"
    >

    <div class="login-field">
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

    <div class="login-field">
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

    <button type="submit" class="login-submit">
        Log in
    </button>

</form>

<p class="login-forgot">
    <a href="/forgot-password">Forgot your password?</a>
</p>

</section>
