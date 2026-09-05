<?php

declare(strict_types=1);

/**
 * @var array{
 *     items: list<array<string, mixed>>,
 *     total: int,
 *     page: int,
 *     per_page: int
 * } $available
 * @var array{
 *     items: list<array<string, mixed>>,
 *     total: int,
 *     page: int,
 *     per_page: int
 * } $curated
 * @var list<array<string, mixed>> $categories
 * @var int|null $selectedCategoryId
 * @var array<string, mixed>|null $latestOrder
 * @var array<string, string>|null $flash
 * @var string|null $userName
 * @var int $availablePage
 * @var int $curatedPage
 */

$userName = trim((string) ($userName ?? ''));
$availableTotal = (int) ($available['total'] ?? 0);

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<?php require dirname(__DIR__, 2) . '/components/catalog-assets.php'; ?>

<section class="catalog-home" aria-labelledby="catalog-heading" data-catalog-root>
    <header class="catalog-hero" data-purpose="hero-banner">
        <div class="catalog-hero-orb" aria-hidden="true"></div>
        <div class="catalog-hero-copy">
            <span class="catalog-eyebrow">Cafeteria Desk</span>
            <h1 id="catalog-heading"><?= $userName !== ''
                ? 'Good day, ' . $e($userName)
                : 'Today’s menu'
            ?></h1>
            <p class="catalog-lede">
                Choose drinks and snacks for your room. Cart totals are a preview —
                the kitchen confirms final prices at checkout.
            </p>
            <div class="catalog-hero-actions">
                <a href="/orders/new" class="btn catalog-btn-light">Start an order</a>
                <a href="/orders" class="btn catalog-btn-ghost">My orders</a>
            </div>
        </div>
    </header>

    <?php if ($latestOrder !== null): ?>
        <aside class="latest-order-panel" aria-label="Latest order" data-purpose="latest-order-widget">
            <div class="latest-order-grid">
                <div>
                    <p class="latest-order-kicker">Latest Order</p>
                    <p class="latest-order-total mb-0">
                        <?= $e($latestOrder['total_amount'] ?? '0.00') ?>
                        <span>EGP</span>
                    </p>
                </div>
                <div>
                    <p class="latest-order-kicker">Status</p>
                    <?php
                    $status = (string) ($latestOrder['status'] ?? '');
                    require dirname(__DIR__, 2) . '/components/order-status-badge.php';
                    ?>
                </div>
                <div class="latest-order-room">
                    <p class="latest-order-kicker">Room</p>
                    <p class="latest-order-room-name mb-0"><?= $e($latestOrder['room_name'] ?? '—') ?></p>
                </div>
                <div class="latest-order-cta">
                    <?php if (!empty($latestOrder['id'])): ?>
                        <a
                            class="btn catalog-btn-outline"
                            href="/orders/<?= (int) $latestOrder['id'] ?>"
                        >
                            View details
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </aside>
    <?php endif; ?>

    <?php if ($availableTotal === 0): ?>
        <div class="catalog-empty" role="status">
            <h2 class="h5 mb-2">Kitchen is quiet</h2>
            <p class="text-muted mb-0">No products are available right now. Check back soon.</p>
        </div>
    <?php else: ?>
        <div class="catalog-dashboard">
            <div class="catalog-main" data-catalog-panels>
                <?php require __DIR__ . '/partials/available.php'; ?>
                <?php require __DIR__ . '/partials/curated.php'; ?>
            </div>

            <aside class="catalog-aside" data-purpose="cart-summary">
                <?php require dirname(__DIR__, 2) . '/components/cart-summary.php'; ?>
            </aside>
        </div>
    <?php endif; ?>
</section>
