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

$notes = (string) ($old['notes'] ?? '');

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

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
                        <option value="<?= $e($room['id']) ?>">
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

            <div
                id="order-items"
                aria-live="polite"
            ></div>

            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold">Total</span>
                        <span id="order-total">0.00</span>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
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

    <?php endif; ?>
</section>