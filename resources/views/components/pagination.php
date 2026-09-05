<?php

declare(strict_types=1);

/** @var array<string, mixed> $categories */
/** @var array<string, mixed> $users */

$data = $categories ?? $users ?? [];

$page = max(1, (int) ($data['page'] ?? 1));
$perPage = max(1, (int) ($data['per_page'] ?? 15));
$total = max(0, (int) ($data['total'] ?? 0));

$totalPages = $perPage > 0
    ? max(1, (int) ceil($total / $perPage))
    : 1;

if ($total === 0) {
    return;
}

$currentPath = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
) ?: '/';

$query = $_GET;

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<nav aria-label="Pagination" class="mt-3 admin-pagination">
    <ul class="pagination mb-0">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <?php if ($page > 1): ?>
                <?php $query['page'] = $page - 1; ?>
                <a class="page-link" href="<?= $e($currentPath . '?' . http_build_query($query)) ?>">
                    Previous
                </a>
            <?php else: ?>
                <span class="page-link">Previous</span>
            <?php endif; ?>
        </li>

        <li class="page-item disabled">
            <span class="page-link">Page <?= $page ?> of <?= $totalPages ?></span>
        </li>

        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <?php if ($page < $totalPages): ?>
                <?php $query['page'] = $page + 1; ?>
                <a class="page-link" href="<?= $e($currentPath . '?' . http_build_query($query)) ?>">
                    Next
                </a>
            <?php else: ?>
                <span class="page-link">Next</span>
            <?php endif; ?>
        </li>
    </ul>
</nav>
