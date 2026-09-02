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
$selectedRoomId = (int) ($old['room_id'] ?? 0);
$notes = (string) ($old['notes'] ?? '');
$oldItems = is_array($old['items'] ?? null) ? $old['items'] : [];
$firstItem = is_array($oldItems[0] ?? null) ? $oldItems[0] : [];
$selectedProductId = (int) ($firstItem['product_id'] ?? 0);
$quantity = max(1, (int) ($firstItem['quantity'] ?? 1));

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<section aria-labelledby="order-form-heading">
    <h1 id="order-form-heading" class="h3 mb-4">New order</h1>

    <?php require dirname(__DIR__) . '/components/form-errors.php'; ?>

    <?php if ($productItems === []): ?>
        <div class="alert alert-warning" role="alert">
            No products are available to order.
            <a href="/" class="alert-link">Return to the catalogue</a>.
        </div>
    <?php else: ?>
        <form action="/orders" method="post" class="app-form" novalidate>
            <?php if (isset($csrfField) && is_string($csrfField)): ?>
                <?= $csrfField ?>
            <?php endif; ?>

            <div class="mb-3">
                <label for="room_id" class="form-label">Delivery room</label>
                <select
                    id="room_id"
                    name="room_id"
                    class="form-select"
                    required
                >
                    <option value="">Select a room</option>
                    <?php foreach ($rooms as $room): ?>
                        <option
                            value="<?= $e($room['id']) ?>"
                            <?= $selectedRoomId === $room['id'] ? 'selected' : '' ?>
                        >
                            <?= $e($room['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="product_id" class="form-label">Product</label>
                <select
                    id="product_id"
                    name="items[0][product_id]"
                    class="form-select"
                    required
                >
                    <option value="">Select a product</option>
                    <?php foreach ($productItems as $product): ?>
                        <option
                            value="<?= $e($product['id']) ?>"
                            <?= $selectedProductId === (int) $product['id'] ? 'selected' : '' ?>
                        >
                            <?= $e($product['name']) ?> (<?= $e($product['price']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input
                    type="number"
                    id="quantity"
                    name="items[0][quantity]"
                    class="form-control"
                    min="1"
                    value="<?= $e($quantity) ?>"
                    required
                >
            </div>

            <div class="mb-4">
                <label for="notes" class="form-label">Notes (optional)</label>
                <textarea
                    id="notes"
                    name="notes"
                    class="form-control"
                    rows="3"
                    maxlength="500"
                ><?= $e($notes) ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Place order</button>
                <a href="/" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
</section>
