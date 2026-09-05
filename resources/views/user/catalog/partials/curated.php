<?php

declare(strict_types=1);

/**
 * @var array{
 *     items: list<array<string, mixed>>,
 *     total: int,
 *     page: int,
 *     per_page: int
 * } $curated
 * @var list<array<string, mixed>> $categories
 * @var int|null $selectedCategoryId
 * @var int $availablePage
 * @var int $curatedPage
 */

$curatedItems = $curated['items'] ?? [];
$curatedTotal = (int) ($curated['total'] ?? 0);
$curatedPage = max(1, (int) ($curated['page'] ?? $curatedPage ?? 1));
$curatedPerPage = max(1, (int) ($curated['per_page'] ?? 3));
$curatedPages = max(1, (int) ceil($curatedTotal / $curatedPerPage));
$availablePage = max(1, (int) ($availablePage ?? 1));
$categories = $categories ?? [];
$selectedCategoryId = $selectedCategoryId ?? null;

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

require __DIR__ . '/query.php';
?>
<section
    class="catalog-collection"
    id="catalog-curated"
    aria-labelledby="curated-heading"
    data-catalog-section="curated"
>
    <div class="catalog-collection-head">
        <div>
            <span class="catalog-collection-kicker">Curated Selection</span>
            <h3 id="curated-heading">Luxury Collection</h3>
        </div>
        <?php if ($categories !== []): ?>
            <div class="catalog-chips" role="navigation" aria-label="Filter curated selection">
                <a
                    href="<?= $e($catalogQuery(['category' => null, 'cpage' => 1])) ?>"
                    class="catalog-chip<?= $selectedCategoryId === null ? ' is-active' : '' ?>"
                    data-catalog-ajax
                >
                    All
                </a>
                <?php foreach ($categories as $category): ?>
                    <?php
                    $cid = (int) ($category['id'] ?? 0);
                    $cname = (string) ($category['name'] ?? '');
                    ?>
                    <a
                        href="<?= $e($catalogQuery(['category' => $cid, 'cpage' => 1])) ?>"
                        class="catalog-chip<?= $selectedCategoryId === $cid ? ' is-active' : '' ?>"
                        data-catalog-ajax
                    >
                        <?= $e($cname) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($curatedItems === []): ?>
        <div class="catalog-empty catalog-empty--inline" role="status">
            <p class="mb-0">No products in this selection.</p>
        </div>
    <?php else: ?>
        <div class="catalog-product-grid catalog-product-grid--curated">
            <?php foreach ($curatedItems as $product): ?>
                <?php require dirname(__DIR__, 3) . '/components/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php
    $page = $curatedPage;
    $totalPages = $curatedPages;
    $pageParam = 'cpage';
    $ariaLabel = 'Curated selection pagination';
    require dirname(__DIR__, 3) . '/components/catalog-pagination.php';
    ?>
</section>
