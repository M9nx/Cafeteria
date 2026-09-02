```php
<?php

/**
 * Expected variables:
 *
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

$name = $old['name'] ?? $product['name'] ?? '';
$categoryId = $old['category_id'] ?? $product['category_id'] ?? '';
$price = $old['price'] ?? $product['price'] ?? '';
$isAvailable = $old['is_available'] ?? $product['is_available'] ?? false;

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$action = $isEdit
    ? '/admin/products/' . $e($product['id'] ?? '') . '/update'
    : '/admin/products';
?>

<div class="container py-4">

    <div class="mb-4">
        <h1 class="h3">
            <?= $isEdit ? 'Edit Product' : 'Create Product' ?>
        </h1>
    </div>

    <?php if ($errors !== []): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= $e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form
        method="POST"
        action="<?= $action ?>"
        enctype="multipart/form-data"
    >

        <input
            type="hidden"
            name="_token"
            value="<?= $e($csrfToken) ?>"
        >

        <div class="mb-3">
            <label for="name" class="form-label">
                Product name
            </label>

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
            <label for="category_id" class="form-label">
                Category
            </label>

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
            <label for="price" class="form-label">
                Price
            </label>

            <input
                type="number"
                id="price"
                name="price"
                class="form-control"
                value="<?= $e($price) ?>"
                min="0"
                step="0.01"
                required
            >
        </div>

        <div class="form-check mb-3">
            <input
                type="hidden"
                name="is_available"
                value="0"
            >

            <input
                type="checkbox"
                id="is_available"
                name="is_available"
                value="1"
                class="form-check-input"
                <?= $isAvailable ? 'checked' : '' ?>
            >

            <label
                for="is_available"
                class="form-check-label"
            >
                Available
            </label>
        </div>

        <div class="mb-4">
            <label for="image" class="form-label">
                Product image
            </label>

            <input
                type="file"
                id="image"
                name="image"
                class="form-control"
                accept="image/jpeg,image/png,image/webp"
            >
        </div>

        <div class="d-flex gap-2">

            <button
                type="submit"
                class="btn btn-primary"
            >
                <?= $isEdit ? 'Update Product' : 'Create Product' ?>
            </button>

            <a
                href="/admin/products"
                class="btn btn-secondary"
            >
                Cancel
            </a>

        </div>

    </form>

</div>
```
