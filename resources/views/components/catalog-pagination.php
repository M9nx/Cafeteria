<?php

declare(strict_types=1);

/**
 * @var int $page
 * @var int $totalPages
 * @var callable(array<string, mixed>): string $catalogQuery
 * @var string $pageParam Query key for this section's page ("page" or "cpage")
 * @var string $ariaLabel
 */

$page = max(1, (int) ($page ?? 1));
$totalPages = max(1, (int) ($totalPages ?? 1));
$pageParam = (string) ($pageParam ?? 'page');
$ariaLabel = (string) ($ariaLabel ?? 'Pagination');
$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<nav class="catalog-pagination" aria-label="<?= $e($ariaLabel) ?>">
    <?php if ($page > 1): ?>
        <a
            class="catalog-page-nav"
            href="<?= $e($catalogQuery([$pageParam => $page - 1])) ?>"
            data-catalog-ajax
        >Previous</a>
    <?php else: ?>
        <span class="catalog-page-nav is-disabled">Previous</span>
    <?php endif; ?>

    <span class="catalog-page-sep" aria-hidden="true">|</span>

    <?php
    $windowStart = max(1, $page - 1);
    $windowEnd = min($totalPages, $windowStart + 2);
    $windowStart = max(1, $windowEnd - 2);
    for ($i = $windowStart; $i <= $windowEnd; $i++):
    ?>
        <?php if ($i === $page): ?>
            <span class="catalog-page-num is-active" aria-current="page"><?= $i ?></span>
        <?php else: ?>
            <a
                class="catalog-page-num"
                href="<?= $e($catalogQuery([$pageParam => $i])) ?>"
                data-catalog-ajax
            ><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <span class="catalog-page-sep" aria-hidden="true">|</span>

    <?php if ($page < $totalPages): ?>
        <a
            class="catalog-page-nav"
            href="<?= $e($catalogQuery([$pageParam => $page + 1])) ?>"
            data-catalog-ajax
        >Next</a>
    <?php else: ?>
        <span class="catalog-page-nav is-disabled">Next</span>
    <?php endif; ?>
</nav>
