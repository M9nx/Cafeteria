<?php

declare(strict_types=1);

/** @var list<string>|array<string, string>|null $errors */

if (!isset($errors) || $errors === []) {
    return;
}

$items = [];

if (array_is_list($errors)) {
    foreach ($errors as $error) {
        $message = trim((string) $error);

        if ($message !== '') {
            $items[] = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        }
    }
} else {
    foreach ($errors as $field => $error) {
        $message = trim((string) $error);

        if ($message === '') {
            continue;
        }

        $fieldId = htmlspecialchars((string) $field, ENT_QUOTES, 'UTF-8');
        $escapedMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $items[] = "<a class=\"alert-link\" href=\"#{$fieldId}\">{$fieldId}</a>: {$escapedMessage}";
    }
}

if ($items === []) {
    return;
}
?>
<div class="alert alert-danger app-form-errors" role="alert" aria-live="polite">
    <h2 class="h6 alert-heading mb-2">Please fix the following:</h2>
    <ul class="mb-0 ps-3">
        <?php foreach ($items as $item): ?>
            <li><?= $item ?></li>
        <?php endforeach; ?>
    </ul>
</div>
