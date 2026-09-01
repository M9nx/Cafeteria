<?php

declare(strict_types=1);

/** @var array<string, mixed> $categories */
/** @var array<string, mixed> $users */

$data = $categories ?? $users ?? [];

$page = (int) ($data['page'] ?? 1);
$perPage = (int) ($data['per_page'] ?? 15);
$total = (int) ($data['total'] ?? 0);

$totalPages = $perPage > 0
    ? (int) ceil($total / $perPage)
    : 1;

if ($totalPages <= 1) {
    return;
}

$currentPath = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
) ?: '/';

$query = $_GET;

?>

<nav aria-label="Pagination">

    <?php if ($page > 1): ?>

        <?php
        $query['page'] = $page - 1;

        $previousUrl = $currentPath
            . '?'
            . http_build_query($query);
        ?>

        <a href="<?= htmlspecialchars(
            $previousUrl,
            ENT_QUOTES,
            'UTF-8'
        ) ?>">
            Previous
        </a>

    <?php endif; ?>

    <span>
        Page <?= $page ?> of <?= $totalPages ?>
    </span>

    <?php if ($page < $totalPages): ?>

        <?php
        $query['page'] = $page + 1;

        $nextUrl = $currentPath
            . '?'
            . http_build_query($query);
        ?>

        <a href="<?= htmlspecialchars(
            $nextUrl,
            ENT_QUOTES,
            'UTF-8'
        ) ?>">
            Next
        </a>

    <?php endif; ?>

</nav>