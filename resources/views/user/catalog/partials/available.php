<?php

declare(strict_types=1);

/**
 * @var array{
 *     items: list<array<string, mixed>>,
 *     total: int,
 *     page: int,
 *     per_page: int
 * } $available
 * @var int|null $selectedCategoryId
 * @var int $availablePage
 * @var int $curatedPage
 */

$availableItems = $available['items'] ?? [];
$availableTotal = (int) ($available['total'] ?? 0);
$availablePage = max(1, (int) ($available['page'] ?? $availablePage ?? 1));
$availablePerPage = max(1, (int) ($available['per_page'] ?? 4));
$availablePages = max(1, (int) ceil($availableTotal / $availablePerPage));
$curatedPage = max(1, (int) ($curatedPage ?? 1));
$selectedCategoryId = $selectedCategoryId ?? null;

require __DIR__ . '/query.php';
?>
<section
    class="catalog-available"
    id="catalog-available"
    aria-labelledby="available-heading"
    data-catalog-section="available"
>
    <div class="catalog-section-head">
        <h2 id="available-heading">Available now</h2>
        <span class="catalog-item-count"><?= $availableTotal ?> items</span>
    </div>

    <div class="catalog-product-grid">
        <?php foreach ($availableItems as $product): ?>
            <?php require dirname(__DIR__, 3) . '/components/product-card.php'; ?>
        <?php endforeach; ?>
    </div>

    <?php
    $page = $availablePage;
    $totalPages = $availablePages;
    $pageParam = 'page';
    $ariaLabel = 'Available now pagination';
    require dirname(__DIR__, 3) . '/components/catalog-pagination.php';
    ?>
</section>
