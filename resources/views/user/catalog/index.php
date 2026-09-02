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
$total = (int) ($products['total'] ?? 0);
$page = max(1, (int) ($products['page'] ?? 1));
$perPage = max(1, (int) ($products['per_page'] ?? 15));
$totalPages = max(1, (int) ceil($total / $perPage));

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<?php require dirname(__DIR__, 2) . '/components/catalog-assets.php'; ?>

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
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-2 g-4">
                    <?php foreach ($items as $product): ?>
                        <div class="col">
                            <?php require dirname(__DIR__, 2) . '/components/product-card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="mt-4" aria-label="Catalogue pagination">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <?php if ($page > 1): ?>
                                    <a class="page-link" href="/?page=<?= $page - 1 ?>">Previous</a>
                                <?php else: ?>
                                    <span class="page-link">Previous</span>
                                <?php endif; ?>
                            </li>
                            <li class="page-item disabled">
                                <span class="page-link">
                                    Page <?= $page ?> of <?= $totalPages ?>
                                </span>
                            </li>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <?php if ($page < $totalPages): ?>
                                    <a class="page-link" href="/?page=<?= $page + 1 ?>">Next</a>
                                <?php else: ?>
                                    <span class="page-link">Next</span>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <?php require dirname(__DIR__, 2) . '/components/cart-summary.php'; ?>

                <p class="text-muted small mt-2 mb-3">
                    Cart totals are a preview only. The server validates prices at checkout.
                </p>

                <a href="/orders/new" class="btn btn-primary w-100">
                    Continue to checkout
                </a>
            </div>
        </div>
    <?php endif; ?>
</section>
