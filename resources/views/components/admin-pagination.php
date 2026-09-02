<?php

declare(strict_types=1);

/** @var array<string, mixed> $paginated */

$page = max(1, (int) ($paginated['page'] ?? 1));
$perPage = max(1, (int) ($paginated['per_page'] ?? 15));
$total = max(0, (int) ($paginated['total'] ?? 0));
$totalPages = max(1, (int) ceil($total / $perPage));

if ($totalPages <= 1) {
    return;
}

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$query = $_GET;

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<nav aria-label="Admin pagination" class="mt-4">
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

        <?php for ($currentPage = 1; $currentPage <= $totalPages; $currentPage++): ?>
            <li class="page-item <?= $currentPage === $page ? 'active' : '' ?>">
                <?php if ($currentPage === $page): ?>
                    <span class="page-link" aria-current="page"><?= $currentPage ?></span>
                <?php else: ?>
                    <?php $query['page'] = $currentPage; ?>
                    <a class="page-link" href="<?= $e($currentPath . '?' . http_build_query($query)) ?>">
                        <?= $currentPage ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endfor; ?>

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
