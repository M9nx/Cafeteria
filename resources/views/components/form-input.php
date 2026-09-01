<?php

declare(strict_types=1);

/** @var string|null $name */
/** @var string|null $label */
/** @var string|null $type */
/** @var string|null $value */
/** @var bool|null $required */
/** @var string|null $autocomplete */
/** @var string|null $message */

$name = trim((string) ($name ?? ''));

if ($name === '') {
    return;
}

$label = (string) ($label ?? '');
$type = (string) ($type ?? 'text');
$value = (string) ($value ?? '');
$required = (bool) ($required ?? false);
$autocomplete = isset($autocomplete) ? trim((string) $autocomplete) : null;
$message = trim((string) ($message ?? ''));

$id = $name;
$hasError = $message !== '';
$escapedId = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
$escapedLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
$escapedType = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
$escapedName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
?>
<div class="mb-3">
    <label for="<?= $escapedId ?>" class="form-label"><?= $escapedLabel ?></label>
    <input
        type="<?= $escapedType ?>"
        id="<?= $escapedId ?>"
        name="<?= $escapedName ?>"
        class="form-control<?= $hasError ? ' is-invalid' : '' ?>"
        value="<?= $escapedValue ?>"
        <?= $required ? 'required' : '' ?>
        <?= $autocomplete !== null && $autocomplete !== ''
            ? 'autocomplete="' . htmlspecialchars($autocomplete, ENT_QUOTES, 'UTF-8') . '"'
            : '' ?>
        <?= $hasError ? 'aria-describedby="' . $escapedId . '-error"' : '' ?>
    >
    <?php
    $fieldId = $id;
    require __DIR__ . '/field-error.php';
    ?>
</div>
