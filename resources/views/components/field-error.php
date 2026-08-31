<?php

declare(strict_types=1);

/** @var string|null $fieldId */
/** @var string|null $message */

$fieldId = trim((string) ($fieldId ?? ''));

if ($fieldId === '') {
    return;
}

$message = trim((string) ($message ?? ''));

if ($message === '') {
    return;
}

$escapedId = htmlspecialchars($fieldId, ENT_QUOTES, 'UTF-8');
$escapedMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
?>
<div id="<?= $escapedId ?>-error" class="invalid-feedback d-block app-field-error">
    <?= $escapedMessage ?>
</div>
