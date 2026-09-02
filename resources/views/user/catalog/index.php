<?php

declare(strict_types=1);

/**
 * @var array{
 *     items: list<array<string, mixed>>,
 *     total: int,
 *     page: int,
 *     per_page: int
 * } $products
 * @var array<string, mixed>|null $latestOrder
 */

$items = $products['items'] ?? [];

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<section aria-labelledby="catalog-heading">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 id="catalog-heading" class="h3 mb-0">Catalogue</h1>
        <a href="/orders/new" class="btn btn-primary">New order</a>
    </div>

    <?php if ($latestOrder !== null): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h6 card-title mb-2">Latest order</h2>

                <p class="mb-1">
                    <span class="text-muted">Status:</span>
                    <?= $e($latestOrder['status'] ?? '') ?>
                </p>

                <p class="mb-1">
                    <span class="text-muted">Room:</span>
                    <?= $e($latestOrder['room_name'] ?? '') ?>
                </p>

                <p class="mb-0">
                    <span class="text-muted">Total:</span>
                    <?= $e($latestOrder['total_amount'] ?? '0.00') ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($items === []): ?>

        <div class="alert alert-info" role="status">
            No products are available right now.
        </div>

    <?php else: ?>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($items as $product): ?>
                <div class="col">
                    <?php
                    require __DIR__ . '/../../components/product-card.php';
                    ?>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</section>