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
    class="product-card"
    data-purpose="product-card"
    data-product-id="<?= $productId ?>"
    data-product-name="<?= $e($name) ?>"
    data-product-price="<?= $e($price) ?>"
>
    <div class="product-card-media">
        <img
            src="<?= $e($imagePath) ?>"
            alt="<?= $e($name) ?>"
            loading="lazy"
        >
    </div>

    <div class="product-card-body">
        <div>
            <?php if ($category !== ''): ?>
                <p class="product-category">
                    <?= $e($category) ?>
                </p>
            <?php endif; ?>

            <h3 class="product-card-title">
                <?= $e($name) ?>
            </h3>

            <p class="product-price">
                <span class="product-price-value"><?= $e($price) ?></span>
                <span class="product-price-currency">EGP</span>
            </p>
        </div>

        <button
            type="button"
            class="btn product-add-btn add-to-cart"
            data-product-id="<?= $productId ?>"
        >
            Add to cart
        </button>
    </div>
</article>
