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
?>

<?php require dirname(__DIR__, 2) . '/components/catalog-assets.php'; ?>

<section aria-labelledby="order-create-heading">
    <h1 id="order-create-heading" class="h3 mb-4">
        New order
    </h1>

    <?php require dirname(__DIR__, 2) . '/components/form-errors.php'; ?>

    <?php if ($productItems === []): ?>
        <div class="alert alert-warning" role="alert">
            No products are available to order.
            <a href="/" class="alert-link">Return to the catalogue</a>.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-7">
                <h2 class="h5 mb-3">Available products</h2>

                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <?php foreach ($productItems as $product): ?>
                        <div class="col">
                            <?php require dirname(__DIR__, 2) . '/components/product-card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-lg-5">
                <form
                    action="/orders"
                    method="post"
                    class="app-form"
                    id="order-form"
                >
                    <?php if (isset($csrfField) && is_string($csrfField)): ?>
                        <?= $csrfField ?>
                    <?php endif; ?>

                    <div class="mb-4">
                        <label for="room_id" class="form-label">
                            Delivery room
                        </label>

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
                                    <?= $selectedRoomId === (int) $room['id'] ? 'selected' : '' ?>
                                >
                                    <?= $e($room['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label">
                            Notes (optional)
                        </label>

                        <textarea
                            id="notes"
                            name="notes"
                            class="form-control"
                            rows="3"
                            maxlength="500"
                        ><?= $e($notes) ?></textarea>
                    </div>

                    <h2 class="h5 mb-3">Your order</h2>

                    <div
                        id="order-items"
                        class="mb-3"
                        aria-live="polite"
                    ></div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between fw-semibold">
                                <span>Total preview</span>
                                <span id="order-total">0.00</span>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted small">
                        The total shown here is a preview. The server calculates the final amount.
                    </p>

                    <div class="d-flex gap-2">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Place order
                        </button>

                        <a
                            href="/"
                            class="btn btn-outline-secondary"
                        >
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</section>
