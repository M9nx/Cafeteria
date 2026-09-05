<?php

declare(strict_types=1);

/** @var string $mode */
/** @var array<string, mixed>|null $category */
/** @var list<string> $errors */
/** @var string $csrfToken */
/** @var array<string, mixed> $old */

$isEdit = $mode === 'edit';
$category = $category ?? [];
$old = $old ?? [];
$errors = $errors ?? [];

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$name = $old['name'] ?? $category['name'] ?? '';
$isActive = array_key_exists('is_active', $old)
    ? ($old['is_active'] === '1' || $old['is_active'] === 1 || $old['is_active'] === true)
    : (bool) ($category['is_active'] ?? true);

$action = $isEdit
    ? '/admin/categories/' . (int) ($category['id'] ?? 0) . '/update'
    : '/admin/categories';
?>

<div class="page-heading">
    <div>
        <h1 class="h3"><?= $isEdit ? 'Edit category' : 'Create category' ?></h1>
        <p>
            <?= $isEdit
                ? 'Update the category name or availability.'
                : 'Add a category for catalogue products.'
            ?>
        </p>
    </div>

    <a href="/admin/categories" class="btn btn-outline-secondary">
        Back to categories
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
                maxlength="120"
            >
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
            <?= $isEdit ? 'Update category' : 'Create category' ?>
        </button>
        <a href="/admin/categories" class="btn btn-outline-secondary">
            Cancel
        </a>
    </div>
</form>
