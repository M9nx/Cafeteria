<?php

declare(strict_types=1);

/** @var array{type?: string, message?: string}|null $flash */

if (!isset($flash) || !is_array($flash)) {
    return;
}

$message = trim((string) ($flash['message'] ?? ''));

if ($message === '') {
    return;
}

$type = strtolower((string) ($flash['type'] ?? 'info'));
$allowedTypes = ['success', 'danger', 'warning', 'info'];
$alertType = in_array($type, $allowedTypes, true) ? $type : 'info';
$escapedMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
?>
<div
    class="alert alert-<?= $alertType ?> alert-dismissible fade show app-alert"
    role="alert"
>
    <?= $escapedMessage ?>
    <button
        type="button"
        class="btn-close"
        data-bs-dismiss="alert"
        aria-label="Close message"
    ></button>
</div>
