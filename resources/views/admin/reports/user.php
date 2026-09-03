<?php

declare(strict_types=1);

/** @var array<string, mixed> $drillDown */
/** @var array<string, mixed> $filters */
/** @var list<string> $errors */

$user = $drillDown['user'] ?? [];
$orders = $drillDown['orders'] ?? [];
$summary = $drillDown['summary'] ?? [];
$errors = $errors ?? [];
$filters = $filters ?? [];

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$query = static function (array $filters): string {
    $params = [];

    foreach (['user_id', 'from', 'to'] as $key) {
        $value = trim((string) ($filters[$key] ?? ''));

        if ($value !== '') {
            $params[$key] = $value;
        }
    }

    if (!empty($filters['include_cancelled'])) {
        $params['include_cancelled'] = '1';
    }

    if ($params === []) {
        return '';
    }

    return '?' . http_build_query($params);
};

$backFilters = $filters;
unset($backFilters['user_id']);

$exportQuery = $query([
    'user_id' => $user['id'] ?? ($filters['user_id'] ?? ''),
    'from' => $filters['from'] ?? '',
    'to' => $filters['to'] ?? '',
    'include_cancelled' => $filters['include_cancelled'] ?? false,
]);
?>

<section aria-labelledby="checks-drilldown-heading">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-4">
        <div>
            <h1 id="checks-drilldown-heading" class="h3 mb-1">
                Checks drill-down
            </h1>

            <p class="text-body-secondary mb-0">
                Review orders and totals for the selected user.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a
                href="/admin/checks<?= $e($query($backFilters)) ?>"
                class="btn btn-outline-secondary"
            >
                Back to summary
            </a>

            <?php if ($errors === []): ?>
                <a
                    href="/admin/checks/export<?= $e($exportQuery) ?>"
                    class="btn btn-outline-secondary"
                >
                    Export CSV
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php require dirname(__DIR__, 2) . '/components/report-assets.php'; ?>

    <?php require dirname(__DIR__, 2) . '/components/form-errors.php'; ?>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5 card-title">User details</h2>

            <dl class="row mb-0">
                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9">
                    <?= $e($user['name'] ?? '') ?>
                </dd>

                <dt class="col-sm-3">Email</dt>
                <dd class="col-sm-9">
                    <?= $e($user['email'] ?? '') ?>
                </dd>

                <dt class="col-sm-3">Orders</dt>
                <dd class="col-sm-9">
                    <?= (int) ($summary['order_count'] ?? 0) ?>
                </dd>

                <dt class="col-sm-3">Total amount</dt>
                <dd class="col-sm-9">
                    <?= $e($summary['total_amount'] ?? '0.00') ?>
                </dd>
            </dl>
        </div>
    </div>

    <?php if ($orders === []): ?>

        <div
            class="alert alert-info"
            role="status"
            aria-live="polite"
        >
            No orders match the selected filters for this user.
        </div>

    <?php else: ?>

        <div class="table-responsive reports-table-wrapper">
            <table class="table table-striped table-hover align-middle mb-0">
                <caption class="visually-hidden">
                    Orders for <?= $e($user['name'] ?? 'selected user') ?>
                </caption>

                <thead>
                    <tr>
                        <th scope="col">Order</th>
                        <th scope="col">Room</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end">Total</th>
                        <th scope="col">Created</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <th scope="row">
                                #<?= (int) ($order['id'] ?? 0) ?>
                            </th>

                            <td>
                                <?= $e($order['room_name'] ?? '') ?>
                            </td>

                            <td>
                                <?= $e($order['status'] ?? '') ?>
                            </td>

                            <td class="text-end">
                                <?= $e($order['total_amount'] ?? '0') ?>
                            </td>

                            <td>
                                <?= $e($order['created_at'] ?? '') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</section>
