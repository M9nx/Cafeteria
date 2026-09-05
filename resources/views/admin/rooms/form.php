<?php

declare(strict_types=1);

/** @var string $mode */
/** @var array<string, mixed>|null $room */
/** @var list<string> $errors */
/** @var string $csrfToken */
/** @var array<string, mixed> $old */

$isEdit = $mode === 'edit';
$room = $room ?? [];
$old = $old ?? [];
$errors = $errors ?? [];

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$name = $old['name'] ?? $room['name'] ?? '';
$isActive = array_key_exists('is_active', $old)
    ? ($old['is_active'] === '1' || $old['is_active'] === 1 || $old['is_active'] === true)
    : (bool) ($room['is_active'] ?? true);

$action = $isEdit
    ? '/admin/rooms/' . (int) ($room['id'] ?? 0) . '/update'
    : '/admin/rooms';
?>

<div class="page-heading">
    <div>
        <h1 class="h3"><?= $isEdit ? 'Edit room' : 'Create room' ?></h1>
        <p>
            <?= $isEdit
                ? 'Update the room name or availability.'
                : 'Add a room for order delivery and user assignment.'
            ?>
        </p>
    </div>

    <a href="/admin/rooms" class="btn btn-outline-secondary">
        Back to rooms
    </a>
</div>

<?php require dirname(__DIR__, 2) . '/components/form-errors.php'; ?>

<form method="POST" action="<?= $e($action) ?>" class="card">
    <div class="card-body">
        <input
            type="hidden"
            name="_csrf_token"
            value="<?= $e($csrfToken) ?>"
        >

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input
                type="text"
                id="name"
                name="name"
                class="form-control"
                value="<?= $e($name) ?>"
                required
                maxlength="100"
            >
            <div class="form-text">
                Unique room label, e.g. Room 101 or Reception.
            </div>
        </div>

        <?php if ($isEdit): ?>
            <div class="form-check mb-0">
                <input type="hidden" name="is_active" value="0">
                <input
                    type="checkbox"
                    id="is_active"
                    name="is_active"
                    value="1"
                    class="form-check-input"
                    <?= $isActive ? 'checked' : '' ?>
                >
                <label for="is_active" class="form-check-label">
                    Active
                </label>
            </div>
        <?php endif; ?>
    </div>

    <div class="card-footer d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Update room' : 'Create room' ?>
        </button>
        <a href="/admin/rooms" class="btn btn-outline-secondary">
            Cancel
        </a>
    </div>
</form>
