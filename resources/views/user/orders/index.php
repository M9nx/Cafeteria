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
?>

<section aria-labelledby="orders-heading">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 id="orders-heading" class="h3 mb-0">My orders</h1>
        <a href="/orders/new" class="btn btn-primary">New order</a>
    </div>

    <?php
    $flashMessages = $flashMessages ?? null;
    require dirname(__DIR__, 2) . '/components/admin-flash.php';
    require dirname(__DIR__, 2) . '/components/form-errors.php';
    ?>

    <form method="GET" action="/orders" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="from" class="form-label">From</label>
            <input type="date" id="from" name="from" class="form-control" value="<?= $e($filters['from'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <label for="to" class="form-label">To</label>
            <input type="date" id="to" name="to" class="form-control" value="<?= $e($filters['to'] ?? '') ?>">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-secondary">Filter</button>
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
                        <tr>
                            <td><?= (int) ($order['id'] ?? 0) ?></td>
                            <td><?= $e($order['created_at'] ?? '') ?></td>
                            <td><?= $e($order['room_name'] ?? '') ?></td>
                            <td>
                                 <?php
                               $status = (string) ($order['status'] ?? '');
                              require dirname(__DIR__, 2) . '/components/order-status-badge.php';
                                 ?>
                            </td>
                            <td><?= $e($order['total_amount'] ?? '') ?></td>
                            <td>
                             <a href="/orders/<?= (int) ($order['id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary">
                                           View
                             </a>

                             <?php if (($order['status'] ?? '') === 'PROCESSING'): ?>
                                <form method="POST"
                                action="/orders/<?= (int) ($order['id'] ?? 0) ?>/cancel"
                                class="d-inline">
                                <input
                                   type="hidden"
                                   name="_csrf_token"
                                   value="<?= $e($csrfToken ?? '') ?>"
                                >
                               <button type="submit" class="btn btn-sm btn-outline-danger">
                                         Cancel
                                </button>
                               </form>
                             <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav aria-label="Order history pagination">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="/orders?page=<?= $page - 1 ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    <li class="page-item disabled">
                        <span class="page-link">Page <?= $page ?> of <?= $totalPages ?></span>
                    </li>
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="/orders?page=<?= $page + 1 ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
