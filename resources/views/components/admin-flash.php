<?php

declare(strict_types=1);

/** @var array<string, string>|null $flashMessages */

$messages = $flashMessages ?? [];

if ($messages === []) {
    return;
}

$allowedTypes = ['success', 'danger', 'warning', 'info', 'error'];
?>
<?php foreach ($messages as $type => $message): ?>
    <?php
    $text = trim((string) $message);

    if ($text === '') {
        continue;
    }

    $normalizedType = strtolower((string) $type);
    $alertType = $normalizedType === 'error' ? 'danger' : $normalizedType;

    if (!in_array($alertType, $allowedTypes, true)) {
        $alertType = 'info';
    }
    ?>
    <div class="alert alert-<?= htmlspecialchars($alertType, ENT_QUOTES, 'UTF-8') ?>" role="alert">
        <?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endforeach; ?>
