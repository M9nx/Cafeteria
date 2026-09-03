<?php

declare(strict_types=1);

/**
 * @var string $mode
 * @var array<string, mixed>|null $product
 * @var array<int, array<string, mixed>> $categories
 * @var array<int, string> $errors
 * @var string $csrfToken
 * @var array<string, mixed> $old
 */

$isEdit = $mode === 'edit';

$product = $product ?? [];
$old = $old ?? [];
$errors = $errors ?? [];

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$name = $old['name'] ?? $product['name'] ?? '';
$categoryId = $old['category_id'] ?? $product['category_id'] ?? '';
$price = $old['price'] ?? $product['price'] ?? '';
$isAvailable = $old['is_available'] ?? $product['is_available'] ?? false;

if (is_numeric($price) && $price !== '') {
    $price = number_format((float) $price, 2, '.', '');
}

$action = $isEdit
    ? '/admin/products/' . $e($product['id'] ?? '') . '/update'
    : '/admin/products';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">
            <?= $isEdit ? 'Edit product' : 'Create product' ?>
        </h1>
        <p class="text-body-secondary mb-0">
            <?= $isEdit
                ? 'Update catalog details, price, and availability.'
                : 'Add a new item to the cafeteria catalog.'
            ?>
        </p>
    </div>

    <a href="/admin/products" class="btn btn-outline-secondary">
        Back to products
    </a>
</div>

<?php require dirname(__DIR__, 2) . '/components/form-errors.php'; ?>

<form
    method="POST"
    action="<?= $action ?>"
    enctype="multipart/form-data"
    class="card"
>
    <div class="card-body">
        <input
            type="hidden"
            name="_csrf_token"
            value="<?= $e($csrfToken) ?>"
        >

        <div class="mb-3">
            <label for="name" class="form-label">Product name</label>
            <input
                type="text"
                id="name"
                name="name"
                class="form-control"
                value="<?= $e($name) ?>"
                required
            >
        </div>

        <div class="mb-3">
            <label for="category_id" class="form-label">Category</label>
            <select
                id="category_id"
                name="category_id"
                class="form-select"
                required
            >
                <option value="">Select category</option>

                <?php foreach ($categories as $category): ?>
                    <?php
                    $id = $category['id'] ?? '';
                    $categoryName = $category['name'] ?? '';
                    ?>
                    <option
                        value="<?= $e($id) ?>"
                        <?= (string) $categoryId === (string) $id ? 'selected' : '' ?>
                    >
                        <?= $e($categoryName) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="price" class="form-label">Price</label>
            <input
                type="number"
                id="price"
                name="price"
                class="form-control"
                value="<?= $e($price) ?>"
                min="0.01"
                step="0.01"
                required
            >
        </div>

        <div class="form-check mb-3">
            <input type="hidden" name="is_available" value="0">
            <input
                type="checkbox"
                id="is_available"
                name="is_available"
                value="1"
                class="form-check-input"
                <?= $isAvailable ? 'checked' : '' ?>
            >
            <label for="is_available" class="form-check-label">
                Available
            </label>
        </div>

        <div class="mb-0">
            <label for="image" class="form-label">Product image</label>
            <input
                type="file"
                id="image"
                name="image"
                class="form-control"
                accept="image/jpeg,image/png,image/webp"
            >
            <div class="form-text">
                Optional. Leave empty on update to keep the current image.
            </div>
        </div>
    </div>

    <div class="card-footer d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Update product' : 'Create product' ?>
        </button>
        <a href="/admin/products" class="btn btn-outline-secondary">
            Cancel
        </a>
    </div>
</form>
