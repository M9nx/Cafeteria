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
<div
    class="app-flash app-flash-danger app-form-errors ui-notify-banner"
    role="alert"
    aria-live="assertive"
>
    <span class="ui-notify__icon-wrap" aria-hidden="true">
        <svg class="ui-toast__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
        </svg>
    </span>
    <div class="app-flash-body">
        <p class="app-flash-label">Please fix the following</p>
        <ul>
            <?php foreach ($items as $item): ?>
                <li><?= $item ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
