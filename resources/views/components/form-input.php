<?php
$name = $name ?? '';
$label = $label ?? '';
$type = $type ?? 'text';
$value = $value ?? '';
$error = $error ?? null;
$required = $required ?? false;

$id = $name;

$escapedValue = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$escapedLabel = htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8');
$escapedError = htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8');
?>

<div class="mb-3">
    <label for="<?= $id ?>" class="form-label">
        <?= $escapedLabel ?>
    </label>

    <input
        type="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>"
        name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
        id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>"
        value="<?= $escapedValue ?>"
        class="form-control <?= $error ? 'is-invalid' : '' ?>"
        <?= $required ? 'required' : '' ?>
        <?= $error ? 'aria-describedby="' . htmlspecialchars($id . '-error', ENT_QUOTES, 'UTF-8') . '"' : '' ?>
    >

    <?php if ($error): ?>
        <div
            id="<?= htmlspecialchars($id . '-error', ENT_QUOTES, 'UTF-8') ?>"
            class="invalid-feedback"
        >
            <?= $escapedError ?>
        </div>
    <?php endif; ?>
</div>