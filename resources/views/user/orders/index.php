<?php

declare(strict_types=1);

/** @var array<string, mixed> $orders */
/** @var array{from?: string, to?: string} $filters */
/** @var array<string, string>|null $flashMessages */

$items = $orders['items'] ?? [];
$page = max(1, (int) ($orders['page'] ?? 1));
$totalPages = max(
    1,
    (int) ceil(((int) ($orders['total'] ?? 0)) / max(1, (int) ($orders['per_page'] ?? 15))),
);

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$paginationQuery = array_filter(
    [
        'from' => $filters['from'] ?? null,
        'to' => $filters['to'] ?? null,
    ],
    static fn (mixed $value): bool => is_string($value) && $value !== '',
);

$pageUrl = static function (int $targetPage) use ($paginationQuery): string {
    return '/orders?' . http_build_query([
        ...$paginationQuery,
        'page' => $targetPage,
    ]);
};
?>

<?php require dirname(__DIR__, 2) . '/components/order-assets.php'; ?>

<section class="order-history" aria-labelledby="orders-heading">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 id="orders-heading" class="h3 mb-0">My orders</h1>
        <a href="/orders/new" class="btn btn-primary">New order</a>
    </div>

    <?php
    $flashMessages = $flashMessages ?? null;
    require dirname(__DIR__, 2) . '/components/admin-flash.php';
    require dirname(__DIR__, 2) . '/components/form-errors.php';
    ?>

    <form
        method="GET"
        action="/orders"
        class="app-filter-bar order-filter-bar row g-3"
        aria-label="Order date filters"
    >
        <div class="col-md-4">
            <label for="from" class="form-label">From</label>
            <input
                type="date"
                id="from"
                name="from"
                class="form-control"
                value="<?= $e($filters['from'] ?? '') ?>"
                autocomplete="off"
            >
        </div>
        <div class="col-md-4">
            <label for="to" class="form-label">To</label>
            <input
                type="date"
                id="to"
                name="to"
                class="form-control"
                value="<?= $e($filters['to'] ?? '') ?>"
                autocomplete="off"
            >
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    <?php if ($items === []): ?>
        <div class="alert alert-info" role="status">No orders found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Date</th>
                        <th scope="col">Room</th>
                        <th scope="col">Status</th>
                        <th scope="col">Total</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $order): ?>
                        <?php
                        $orderId = (int) ($order['id'] ?? 0);
                        $detailsId = 'order-details-' . $orderId;
                        $status = (string) ($order['status'] ?? '');
                        ?>
                        <tr>
                            <td><?= $orderId ?></td>
                            <td><?= $e($order['created_at'] ?? '') ?></td>
                            <td><?= $e($order['room_name'] ?? '') ?></td>
                            <td>
                                <?php require dirname(__DIR__, 2) . '/components/order-status-badge.php'; ?>
                            </td>
                            <td><?= $e($order['total_amount'] ?? '') ?></td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        data-order-details-toggle
                                        aria-expanded="false"
                                        aria-controls="<?= $e($detailsId) ?>"
                                    >
                                        Details
                                    </button>
                                    <a
                                        href="/orders/<?= $orderId ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        View
                                    </a>
                                    <?php if ($status === 'PROCESSING'): ?>
                                        <form
                                            method="POST"
                                            action="/orders/<?= $orderId ?>/cancel"
                                            class="d-inline"
                                            data-confirm="Cancel this order? This cannot be undone."
                                            data-confirm-title="Cancel order"
                                            data-confirm-label="Cancel order"
                                            data-confirm-tone="danger"
                                        >
                                            <?php if (isset($csrfField) && is_string($csrfField)): ?>
                                                <?= $csrfField ?>
                                            <?php endif; ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                Cancel
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <tr id="<?= $e($detailsId) ?>" hidden>
                            <td colspan="6">
                                <div class="border rounded p-3 bg-light">
                                    <?php if (!empty($order['notes'])): ?>
                                        <p class="mb-2">
                                            <span class="text-muted">Notes:</span>
                                            <?= $e($order['notes']) ?>
                                        </p>
                                    <?php endif; ?>
                                    <a href="/orders/<?= $orderId ?>">Open full order detail</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <nav aria-label="Order history pagination" class="mt-3 admin-pagination">
            <ul class="pagination mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <?php if ($page > 1): ?>
                        <a class="page-link" href="<?= $e($pageUrl($page - 1)) ?>">Previous</a>
                    <?php else: ?>
                        <span class="page-link">Previous</span>
                    <?php endif; ?>
                </li>
                <li class="page-item disabled">
                    <span class="page-link">Page <?= $page ?> of <?= $totalPages ?></span>
                </li>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <?php if ($page < $totalPages): ?>
                        <a class="page-link" href="<?= $e($pageUrl($page + 1)) ?>">Next</a>
                    <?php else: ?>
                        <span class="page-link">Next</span>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</section>
