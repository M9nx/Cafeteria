<?php

declare(strict_types=1);

/** @var array<string, mixed> $summary */
/** @var array<string, mixed> $filters */
/** @var list<string> $errors */

$rows = $summary['users'] ?? [];
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

$userId = $filters['user_id'] ?? '';
$from = $filters['from'] ?? '';
$to = $filters['to'] ?? '';
$includeCancelled = !empty($filters['include_cancelled']);

$totalOrders = $summary['total_orders'] ?? 0;
$totalAmount = $summary['total_amount'] ?? '0';
?>

<section aria-labelledby="checks-report-heading">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h1 id="checks-report-heading" class="h3 mb-1">
                Checks report
            </h1>

            <p class="text-body-secondary mb-0">
                Review order totals by user and drill down into individual checks.
            </p>
        </div>
    </div>

    <?php require dirname(__DIR__, 2) . '/components/form-errors.php'; ?>

    <form
        method="GET"
        action="/admin/checks"
        class="row g-3 mb-4"
        aria-label="Report filters"
    >
        <div class="col-md-3">
            <label for="user_id" class="form-label">User ID</label>

            <input
                type="number"
                id="user_id"
                name="user_id"
                class="form-control"
                min="1"
                value="<?= $e($userId) ?>"
            >
        </div>

        <div class="col-md-3">
            <label for="from" class="form-label">From</label>

            <input
                type="date"
                id="from"
                name="from"
                class="form-control"
                value="<?= $e($from) ?>"
            >
        </div>

        <div class="col-md-3">
            <label for="to" class="form-label">To</label>

            <input
                type="date"
                id="to"
                name="to"
                class="form-control"
                value="<?= $e($to) ?>"
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
                    <?= $includeCancelled ? 'checked' : '' ?>
                >

                <label for="include_cancelled" class="form-check-label">
                    Include cancelled
                </label>
            </div>
        </div>

        <div class="col-12 d-flex flex-wrap gap-2 reports-filter-actions">
            <button type="submit" class="btn btn-primary">
                Apply filters
            </button>

            <a
                href="/admin/checks"
                class="btn btn-outline-secondary"
            >
                Clear filters
            </a>

            <?php if ($errors === []): ?>
                <a
                    href="/admin/checks/export<?= $e($query($filters)) ?>"
                    class="btn btn-outline-secondary"
                >
                    Export CSV
                </a>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($rows === []): ?>

        <div
            class="alert alert-info reports-empty-state"
            role="status"
            aria-live="polite"
        >
            No report rows match the selected filters.
        </div>

    <?php else: ?>

        <div class="mb-3">
            <label for="report-search" class="form-label">
                Search report rows
            </label>

            <input
                type="search"
                id="report-search"
                class="form-control"
                placeholder="Search by user, ID, orders, or amount"
                autocomplete="off"
                data-report-search
                aria-describedby="report-search-status"
            >

            <div
                id="report-search-status"
                class="form-text"
                data-report-search-status
                aria-live="polite"
            >
                Showing <?= count($rows) ?> report row(s).
            </div>
        </div>

        <?php require dirname(__DIR__, 2) . '/components/report-summary-table.php'; ?>

        <div
            class="alert alert-info reports-empty-state d-none"
            role="status"
            aria-live="polite"
            data-report-search-empty
        >
            No report rows match your search.
        </div>

    <?php endif; ?>

</section>
