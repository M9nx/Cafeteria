<?php

declare(strict_types=1);

/** @var array<string, mixed> $drillDown */
/** @var array<string, mixed> $filters */
/** @var list<string> $errors */

$user = $drillDown['user'] ?? [];
$orders = $drillDown['orders'] ?? [];
$summary = $drillDown['summary'] ?? [];
$errors = $errors ?? [];

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$query = static function (array $filters) use ($e): string {
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
?>

<section aria-labelledby="checks-drilldown-heading">
    <h1 id="checks-drilldown-heading" class="h3 mb-3">
        Checks drill-down
    </h1>

    <p class="mb-4 d-flex gap-3">
        <a href="/admin/checks<?= $e($query($filters)) ?>">
            Back to summary
        </a>

        <?php if ($errors === []): ?>
            <?php
            $exportQuery = $query([
                'user_id' => $user['id'] ?? ($filters['user_id'] ?? ''),
                'from' => $filters['from'] ?? '',
                'to' => $filters['to'] ?? '',
                'include_cancelled' => $filters['include_cancelled'] ?? false,
            ]);
            ?>
            <a href="/admin/checks/export<?= $e($exportQuery) ?>">
                Export CSV
            </a>
        <?php endif; ?>
    </p>

    <?php require dirname(__DIR__, 2) . '/components/form-errors.php'; ?>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5 card-title">
                User
            </h2>

            <dl class="row mb-0">
                <dt class="col-sm-3">
                    Name
                </dt>

                <dd class="col-sm-9">
                    <?= $e($user['name'] ?? '') ?>
                </dd>

                <dt class="col-sm-3">
                    Email
                </dt>

                <dd class="col-sm-9">
                    <?= $e($user['email'] ?? '') ?>
                </dd>

                <dt class="col-sm-3">
                    Orders
                </dt>

                <dd class="col-sm-9">
                    <?= (int) ($summary['order_count'] ?? 0) ?>
                </dd>

                <dt class="col-sm-3">
                    Total amount
                </dt>

                <dd class="col-sm-9">
                    <?= $e($summary['total_amount'] ?? '0.00') ?>
                </dd>
            </dl>
        </div>
    </div>

    <?php if ($orders === []): ?>

        <div class="alert alert-info" role="status">
            No orders match the selected filters for this user.
        </div>

    <?php else: ?>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th scope="col">Order</th>
                        <th scope="col">Room</th>
                        <th scope="col">Status</th>
                        <th scope="col">Total</th>
                        <th scope="col">Created</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($orders as $order): ?>

                        <tr>
                            <td>
                                #<?= (int) ($order['id'] ?? 0) ?>
                            </td>

                            <td>
                                <?= $e($order['room_name'] ?? '') ?>
                            </td>

                            <td>
                                <?= $e($order['status'] ?? '') ?>
                            </td>

                            <td>
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