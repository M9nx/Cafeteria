<?php

declare(strict_types=1);

/** @var array<string, mixed> $summary */
/** @var array<string, mixed> $filters */
/** @var list<string> $errors */

$rows = $summary['users'] ?? [];
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

<section aria-labelledby="checks-report-heading">
    <h1 id="checks-report-heading" class="h3 mb-4">
        Checks report
    </h1>

    <?php require dirname(__DIR__, 2) . '/components/form-errors.php'; ?>

    <form method="GET" action="/admin/checks" class="row g-3 mb-4">
        <div class="col-md-3">
            <label for="user_id" class="form-label">
                User ID
            </label>

            <input
                type="number"
                id="user_id"
                name="user_id"
                class="form-control"
                min="1"
                value="<?= $e($filters['user_id'] ?? '') ?>"
            >
        </div>

        <div class="col-md-3">
            <label for="from" class="form-label">
                From
            </label>

            <input
                type="date"
                id="from"
                name="from"
                class="form-control"
                value="<?= $e($filters['from'] ?? '') ?>"
            >
        </div>

        <div class="col-md-3">
            <label for="to" class="form-label">
                To
            </label>

            <input
                type="date"
                id="to"
                name="to"
                class="form-control"
                value="<?= $e($filters['to'] ?? '') ?>"
            >
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <div class="form-check mb-2">
                <input
                    type="checkbox"
                    id="include_cancelled"
                    name="include_cancelled"
                    value="1"
                    class="form-check-input"
                    <?= !empty($filters['include_cancelled']) ? 'checked' : '' ?>
                >

                <label
                    for="include_cancelled"
                    class="form-check-label"
                >
                    Include cancelled
                </label>
            </div>
        </div>

        <div class="col-12 d-flex gap-2">
            <button
                type="submit"
                class="btn btn-primary"
            >
                Apply filters
            </button>

            <a
                href="/admin/checks/export<?= $e($query($filters)) ?>"
                class="btn btn-outline-secondary"
            >
                Export CSV
            </a>
        </div>
    </form>

    <?php if ($rows === []): ?>

        <div class="alert alert-info" role="status">
            No report rows match the selected filters.
        </div>

    <?php else: ?>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th scope="col">User</th>
                        <th scope="col">Orders</th>
                        <th scope="col">Total amount</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($rows as $row): ?>

                        <?php
                        $userId = (int) ($row['user_id'] ?? 0);

                        $drillDownQuery = $query([
                            'user_id' => $userId,
                            'from' => $filters['from'] ?? '',
                            'to' => $filters['to'] ?? '',
                            'include_cancelled' =>
                                $filters['include_cancelled'] ?? false,
                        ]);
                        ?>

                        <tr>
                            <td>
                                <?= $e($row['user_name'] ?? '') ?>
                            </td>

                            <td>
                                <?= (int) ($row['order_count'] ?? 0) ?>
                            </td>

                            <td>
                                <?= $e($row['total_amount'] ?? '0') ?>
                            </td>

                            <td>
                                <a
                                    href="/admin/checks/users/<?= $userId ?><?= $e($drillDownQuery) ?>"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    View details
                                </a>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</section>