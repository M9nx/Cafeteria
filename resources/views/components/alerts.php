<?php

declare(strict_types=1);

/**
 * Accepts either:
 * - array<string, string> from FlashBag::pullAll() (type => message)
 * - array{type?: string, message?: string} single flash
 * - list of array{type?: string, message?: string}
 *
 * @var array<string, mixed>|null $flash
 * @var array<string, string>|null $flashMessages
 */

$raw = $flashMessages ?? $flash ?? null;

if ($raw === null || $raw === []) {
    return;
}

$messages = [];

if (isset($raw['message']) && is_string($raw['message'])) {
    $messages[] = [
        'type' => (string) ($raw['type'] ?? 'info'),
        'message' => $raw['message'],
    ];
} elseif (array_is_list($raw)) {
    foreach ($raw as $entry) {
        if (!is_array($entry)) {
            continue;
        }

        $text = trim((string) ($entry['message'] ?? ''));

        if ($text === '') {
            continue;
        }

        $messages[] = [
            'type' => (string) ($entry['type'] ?? 'info'),
            'message' => $text,
        ];
    }
} else {
    foreach ($raw as $type => $message) {
        if (is_array($message)) {
            $text = trim((string) ($message['message'] ?? ''));
            $entryType = (string) ($message['type'] ?? $type);
        } else {
            $text = trim((string) $message);
            $entryType = (string) $type;
        }

        if ($text === '') {
            continue;
        }

        $messages[] = [
            'type' => $entryType,
            'message' => $text,
        ];
    }
}

if ($messages === []) {
    return;
}

$meta = [
    'success' => [
        'label' => 'Success',
        'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
    ],
    'danger' => [
        'label' => 'Error',
        'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>',
    ],
    'warning' => [
        'label' => 'Warning',
        'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>',
    ],
    'info' => [
        'label' => 'Notice',
        'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>',
    ],
];

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div class="app-flash-stack ui-flash-stack" aria-live="polite" aria-relevant="additions">
    <?php foreach ($messages as $entry): ?>
        <?php
        $type = strtolower($entry['type']);
        $alertType = $type === 'error' ? 'danger' : $type;
        $allowed = ['success', 'danger', 'warning', 'info'];
        $alertType = in_array($alertType, $allowed, true) ? $alertType : 'info';
        $label = $meta[$alertType]['label'];
        $svg = $meta[$alertType]['svg'];
        $role = $alertType === 'danger' ? 'alert' : 'status';
        ?>
        <div
            class="app-flash app-flash-<?= $e($alertType) ?> ui-notify-banner alert alert-<?= $e($alertType) ?> alert-dismissible fade show"
            role="<?= $e($role) ?>"
            data-flash-auto-dismiss="<?= $alertType === 'danger' ? '0' : '8000' ?>"
        >
            <span class="ui-notify__icon-wrap" aria-hidden="true">
                <svg class="ui-toast__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25">
                    <?= $svg ?>
                </svg>
            </span>
            <div class="app-flash-body">
                <p class="app-flash-label"><?= $e($label) ?></p>
                <p class="app-flash-message"><?= $e($entry['message']) ?></p>
            </div>
            <button
                type="button"
                class="app-flash-close btn-close"
                data-bs-dismiss="alert"
                aria-label="Close message"
            >&times;</button>
        </div>
    <?php endforeach; ?>
</div>
