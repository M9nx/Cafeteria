<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $product
 */

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$productId = (int) ($product['id'] ?? 0);
$name = (string) ($product['name'] ?? '');
$category = (string) ($product['category_name'] ?? '');
$price = (string) ($product['price'] ?? '0.00');
$imagePath = \Cafeteria\Support\PublicFileUrl::fromStoredPath(
    isset($product['image_path']) ? (string) $product['image_path'] : null
);
?>

<article
    class="card h-100 product-card"
    data-product-id="<?= $productId ?>"
    data-product-name="<?= $e($name) ?>"
    data-product-price="<?= $e($price) ?>"
>
    <img
        src="<?= $e($imagePath) ?>"
        class="card-img-top"
        alt="<?= $e($name) ?>"
    >

    <div class="card-body d-flex flex-column">
        <h2 class="h5 card-title mb-1">
            <?= $e($name) ?>
        </h2>

        <?php if ($category !== ''): ?>
            <p class="text-muted small mb-2">
                <?= $e($category) ?>
            </p>
        <?php endif; ?>

        <p class="fw-semibold mb-3">
            <?= $e($price) ?>
        </p>

        <button
            type="button"
            class="btn btn-primary mt-auto add-to-cart"
            data-product-id="<?= $productId ?>"
        >
            Add to cart
        </button>
    </div>
</article>