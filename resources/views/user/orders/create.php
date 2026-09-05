<?php

declare(strict_types=1);

/**
 * @var array{
 *     items: list<array<string, mixed>>,
 *     total: int,
 *     page: int,
 *     per_page: int
 * } $products
 * @var list<array{id: int, name: string}> $rooms
 * @var list<string> $errors
 * @var array<string, mixed> $old
 */

$productItems = $products['items'] ?? [];
$rooms = $rooms ?? [];
$errors = $errors ?? [];
$old = $old ?? [];

$selectedRoomId = (int) ($old['room_id'] ?? 0);
$notes = (string) ($old['notes'] ?? '');

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$categories = [];
foreach ($productItems as $product) {
    $name = trim((string) ($product['category_name'] ?? ''));
    if ($name !== '') {
        $categories[$name] = true;
    }
}
$categories = array_keys($categories);
sort($categories);
?>

<link href="/assets/css/orders.css" rel="stylesheet">
<script src="/assets/js/cart.js" defer></script>
<script src="/assets/js/order-create.js" defer></script>

<section class="order-create" aria-labelledby="order-create-heading" data-order-create>
    <div class="order-create-header">
        <div>
            <h1 id="order-create-heading" class="order-create-title">New order</h1>
            <p class="order-create-subtitle">
                Select items from the catalog and configure your delivery details.
            </p>
        </div>

        <?php if ($categories !== []): ?>
            <div class="order-create-chips" data-purpose="category-filters" role="toolbar" aria-label="Filter products">
                <button type="button" class="order-chip is-active" data-category-filter="">
                    All Products
                </button>
                <?php foreach ($categories as $category): ?>
                    <button
                        type="button"
                        class="order-chip"
                        data-category-filter="<?= $e($category) ?>"
                    >
                        <?= $e($category) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php require dirname(__DIR__, 2) . '/components/form-errors.php'; ?>

    <?php if ($productItems === []): ?>
        <div class="alert alert-warning" role="alert">
            No products are available to order.
            <a href="/" class="alert-link">Return to the catalogue</a>.
        </div>
    <?php else: ?>
        <div class="order-create-layout">
            <section class="order-create-catalog" data-purpose="product-catalog-section">
                <div class="order-create-search">
                    <span class="order-create-search-icon" aria-hidden="true">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input
                        type="search"
                        id="order-product-search"
                        class="order-create-search-input"
                        placeholder="Search products by name..."
                        autocomplete="off"
                    >
                </div>

                <div class="order-create-grid" id="order-product-grid">
                    <?php foreach ($productItems as $product): ?>
                        <?php
                        $productId = (int) ($product['id'] ?? 0);
                        $name = (string) ($product['name'] ?? '');
                        $category = (string) ($product['category_name'] ?? '');
                        $price = (string) ($product['price'] ?? '0.00');
                        $imagePath = \Cafeteria\Support\PublicFileUrl::fromStoredPath(
                            isset($product['image_path']) ? (string) $product['image_path'] : null
                        );
                        ?>
                        <article
                            class="product-card order-product-card"
                            data-product-id="<?= $productId ?>"
                            data-product-name="<?= $e($name) ?>"
                            data-product-price="<?= $e($price) ?>"
                            data-product-category="<?= $e($category) ?>"
                        >
                            <div class="order-product-media">
                                <img
                                    src="<?= $e($imagePath) ?>"
                                    alt="<?= $e($name) ?>"
                                    loading="lazy"
                                >
                                <?php if ($category !== ''): ?>
                                    <span class="order-product-badge"><?= $e($category) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="order-product-body">
                                <h3 class="order-product-name"><?= $e($name) ?></h3>
                                <p class="order-product-price">
                                    <?= $e($price) ?>
                                    <span>EGP</span>
                                </p>
                            </div>
                            <div class="order-product-action">
                                <button
                                    type="button"
                                    class="btn order-add-btn add-to-cart"
                                    data-product-id="<?= $productId ?>"
                                    data-add-label="Add to order"
                                >
                                    <span aria-hidden="true">+</span>
                                    <span>Add to order</span>
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <p class="order-create-empty-filter d-none" id="order-filter-empty" role="status">
                    No products match this filter.
                </p>
            </section>

            <section class="order-create-summary" data-purpose="order-summary-section">
                <form
                    action="/orders"
                    method="post"
                    class="app-form order-summary-card"
                    id="order-form"
                >
                    <?php if (isset($csrfField) && is_string($csrfField)): ?>
                        <?= $csrfField ?>
                    <?php endif; ?>

                    <div class="order-summary-head">
                        <h2 class="order-summary-title">Your order</h2>
                        <span class="order-summary-count" id="order-item-count">0 items</span>
                    </div>

                    <div class="order-field">
                        <label for="room_id" class="order-field-label">Delivery room</label>
                        <select
                            id="room_id"
                            name="room_id"
                            class="form-select order-field-control"
                            required
                        >
                            <option value="">Select a room</option>
                            <?php foreach ($rooms as $room): ?>
                                <option
                                    value="<?= $e($room['id']) ?>"
                                    <?= $selectedRoomId === (int) $room['id'] ? 'selected' : '' ?>
                                >
                                    <?= $e($room['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="order-field">
                        <label for="notes" class="order-field-label">Notes (optional)</label>
                        <textarea
                            id="notes"
                            name="notes"
                            class="form-control order-field-control"
                            rows="3"
                            maxlength="500"
                            placeholder="Add any special instructions (e.g. extra sugar, less ice)..."
                        ><?= $e($notes) ?></textarea>
                    </div>

                    <div
                        id="order-items"
                        class="order-items"
                        aria-live="polite"
                        data-purpose="cart-items-container"
                    >
                        <div class="order-items-empty">
                            <p class="order-items-empty-title">Your cart is empty.</p>
                            <p class="order-items-empty-copy">Click “Add to order” on any product to begin.</p>
                        </div>
                    </div>

                    <div class="order-total-box" data-purpose="total-preview-box">
                        <div class="order-total-row">
                            <span>Total preview</span>
                            <span class="order-total-value">
                                <span id="order-total">0.00</span>
                                <span class="order-total-currency">EGP</span>
                            </span>
                        </div>
                        <p class="order-total-hint">
                            The total shown here is a preview. The server calculates the final amount.
                        </p>
                    </div>

                    <div class="order-summary-actions">
                        <button type="submit" class="btn order-submit-btn">
                            Place order
                        </button>
                        <a href="/" class="btn order-cancel-btn">Cancel</a>
                    </div>
                </form>
            </section>
        </div>
    <?php endif; ?>
</section>
