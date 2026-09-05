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
 * @var list<array{id: int, name: string, email: string}> $users
 * @var list<string> $errors
 * @var array<string, mixed> $old
 */

$productItems = $products['items'] ?? [];
$rooms = $rooms ?? [];
$users = $users ?? [];
$errors = $errors ?? [];
$old = $old ?? [];

$selectedUserId = (int) ($old['user_id'] ?? 0);
$selectedRoomId = (int) ($old['room_id'] ?? 0);
$notes = (string) ($old['notes'] ?? '');

$e = static fn (mixed $value): string =>
    htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
?>

<?php
require dirname(__DIR__, 2)
    . '/components/catalog-assets.php';
?>

<section aria-labelledby="admin-order-create-heading">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1
                id="admin-order-create-heading"
                class="h3 mb-1"
            >
                Create order for customer
            </h1>

            <p class="text-muted mb-0">
                Create an order on behalf of an active customer.
            </p>
        </div>

        <a
            href="/admin/orders"
            class="btn btn-outline-secondary"
        >
            Back to orders
        </a>
    </div>

    <?php require dirname(__DIR__, 2) . '/components/form-errors.php'; ?>

    <?php if ($users === []): ?>
        <div
            class="alert alert-warning"
            role="alert"
        >
            No active customers are available for order creation.
        </div>
    <?php elseif ($productItems === []): ?>
        <div
            class="alert alert-warning"
            role="alert"
        >
            No products are available to order.
        </div>
    <?php else: ?>

        <div class="row g-4">
            <div class="col-lg-7">
                <h2 class="h5 mb-3">
                    Available products
                </h2>

                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <?php foreach ($productItems as $product): ?>
                        <div class="col">
                            <?php
                            require dirname(__DIR__, 2)
                                . '/components/product-card.php';
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-lg-5">
                <form
                    action="/admin/orders"
                    method="post"
                    class="app-form"
                    id="order-form"
                >
                    <?php if (
                        isset($csrfField)
                        && is_string($csrfField)
                    ): ?>
                        <?= $csrfField ?>
                    <?php endif; ?>

                    <div class="mb-4">
                        <label
                            for="user_id"
                            class="form-label"
                        >
                            Customer
                        </label>

                        <select
                            id="user_id"
                            name="user_id"
                            class="form-select"
                            required
                        >
                            <option value="">
                                Select a customer
                            </option>

                            <?php foreach ($users as $customer): ?>
                                <option
                                    value="<?= $e($customer['id']) ?>"
                                    <?= $selectedUserId === (int) $customer['id']
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= $e($customer['name']) ?>
                                    -
                                    <?= $e($customer['email']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <div class="form-text">
                            The selected customer will be recorded as
                            the order owner.
                        </div>
                    </div>

                    <div class="mb-4">
                        <span class="form-label" id="admin-room-picker-label">
                            Delivery room
                        </span>
                        <div
                            class="room-picker mt-2"
                            role="radiogroup"
                            aria-labelledby="admin-room-picker-label"
                        >
                            <?php foreach ($rooms as $room): ?>
                                <?php
                                $roomId = (int) ($room['id'] ?? 0);
                                $roomName = (string) ($room['name'] ?? '');
                                $roomImage = \Cafeteria\Support\DemoImageMap::room($roomName);
                                $isSelected = $selectedRoomId === $roomId;
                                ?>
                                <label class="room-pick<?= $isSelected ? ' is-selected' : '' ?>">
                                    <input
                                        type="radio"
                                        name="room_id"
                                        value="<?= $e($roomId) ?>"
                                        class="visually-hidden"
                                        required
                                        <?= $isSelected ? 'checked' : '' ?>
                                    >
                                    <?php if ($roomImage !== null): ?>
                                        <img
                                            src="<?= $e($roomImage) ?>"
                                            alt=""
                                            width="72"
                                            height="54"
                                            loading="lazy"
                                        >
                                    <?php endif; ?>
                                    <span><?= $e($roomName) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label
                            for="notes"
                            class="form-label"
                        >
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

                    <h2 class="h5 mb-3">
                        Order
                    </h2>

                    <div
                        id="order-items"
                        class="mb-3"
                        aria-live="polite"
                    ></div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <div
                                class="d-flex justify-content-between fw-semibold"
                            >
                                <span>
                                    Total preview
                                </span>

                                <span id="order-total">
                                    0.00
                                </span>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted small">
                        The displayed total is only a preview.
                        The server calculates the final amount.
                    </p>

                    <div class="alert alert-info small">
                        <strong>Admin action:</strong>
                        You are creating this order on behalf of
                        the selected customer.
                    </div>

                    <div class="d-flex gap-2">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Create order
                        </button>

                        <a
                            href="/admin/orders"
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