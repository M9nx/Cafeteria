<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $rows */
/** @var int $totalOrders */
/** @var string|int|float $totalAmount */
/** @var array<string, mixed> $filters */

$rows = $rows ?? [];
$totalOrders = $totalOrders ?? 0;
$totalAmount = $totalAmount ?? '0';
$filters = $filters ?? [];

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$from = $filters['from'] ?? '';
$to = $filters['to'] ?? '';
$includeCancelled = !empty($filters['include_cancelled']);

$buildDetailsQuery = static function (
    mixed $from,
    mixed $to,
    bool $includeCancelled
): string {
    $params = [];

    if (trim((string) $from) !== '') {
        $params['from'] = (string) $from;
    }

    if (trim((string) $to) !== '') {
        $params['to'] = (string) $to;
    }

    if ($includeCancelled) {
        $params['include_cancelled'] = '1';
    }

    return $params === []
        ? ''
        : '?' . http_build_query($params);
};
?>

<div class="table-responsive reports-table-wrapper">
    <table
        class="table table-striped table-hover align-middle mb-0 reports-sortable-table"
    >
        <caption class="visually-hidden">
            Checks report summary by user
        </caption>

        <thead>
            <tr>
                <th
                    scope="col"
                    data-sort-key="user"
                    aria-sort="none"
                >
                    User
                </th>

                <th
                    scope="col"
                    class="text-end"
                    data-sort-key="orders"
                    aria-sort="none"
                >
                    Orders
                </th>

                <th
                    scope="col"
                    class="text-end"
                    data-sort-key="amount"
                    aria-sort="none"
                >
                    Total amount
                </th>

                <th scope="col">
                    Details
                </th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($rows as $row): ?>

                <?php
                $rowUserId = $row['user_id'] ?? '';
                $rowUserName = $row['user_name'] ?? '';
                $orderCount = (int) ($row['order_count'] ?? 0);
                $rowAmount = $row['total_amount'] ?? '0';

                $detailsQuery = $buildDetailsQuery(
                    $from,
                    $to,
                    $includeCancelled
                );
                ?>

                <tr data-report-row>
                    <th
                        scope="row"
                        data-sort-value="user"
                    >
                        <span class="fw-semibold">
                            <?= $e($rowUserName) ?>
                        </span>

                        <?php if ($rowUserId !== ''): ?>
                            <small class="text-body-secondary d-block">
                                ID: <?= $e($rowUserId) ?>
                            </small>
                        <?php endif; ?>
                    </th>

                    <td
                        class="text-end"
                        data-sort-value="orders"
                    >
                        <?= $orderCount ?>
                    </td>

                    <td
                        class="text-end"
                        data-sort-value="amount"
                    >
                        <?= $e($rowAmount) ?>
                    </td>

                    <td>
                        <?php if ($rowUserId !== ''): ?>

                            <a
                                href="/admin/checks/users/<?= rawurlencode((string) $rowUserId) ?><?= $e($detailsQuery) ?>"
                                class="btn btn-sm btn-outline-primary"
                                aria-label="View checks for <?= $e($rowUserName !== '' ? $rowUserName : 'selected user') ?>"
                            >
                                View details
                            </a>

                        <?php else: ?>

                            <span class="text-body-secondary">
                                Unavailable
                            </span>

                        <?php endif; ?>
                    </td>
                </tr>

            <?php endforeach; ?>
        </tbody>

        <tfoot>
            <tr class="fw-semibold">
                <th scope="row">
                    Totals
                </th>

                <td class="text-end">
                    <?= $totalOrders ?>
                </td>

                <td class="text-end">
                    <?= $e($totalAmount) ?>
                </td>

                <td>
                    <span class="visually-hidden">
                        Report totals
                    </span>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
