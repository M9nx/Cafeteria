<?php

declare(strict_types=1);

/** @var array<string, mixed> $order */
/** @var bool $canCancel */
/** @var array<string, string>|null $flashMessages */

$items = $order['items'] ?? [];

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<section aria-labelledby="order-detail-heading">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 id="order-detail-heading" class="h3 mb-0">
            Order #<?= (int) ($order['id'] ?? 0) ?>
        </h1>
        <a href="/orders" class="btn btn-secondary">Back to orders</a>
    </div>

    <?php
    $flashMessages = $flashMessages ?? null;
    require dirname(__DIR__, 2) . '/components/admin-flash.php';
    ?>

    <div class="card mb-4">
        <div class="card-body">
            <p class="mb-1"><span class="text-muted">Status:</span> <?= $e($order['status'] ?? '') ?></p>
            <p class="mb-1"><span class="text-muted">Room:</span> <?= $e($order['room_name'] ?? '') ?></p>
            <p class="mb-1"><span class="text-muted">Total:</span> <?= $e($order['total_amount'] ?? '') ?></p>
            <p class="mb-1"><span class="text-muted">Created:</span> <?= $e($order['created_at'] ?? '') ?></p>
            <?php if (!empty($order['notes'])): ?>
                <p class="mb-0"><span class="text-muted">Notes:</span> <?= $e($order['notes']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <h2 class="h5 mb-3">Items</h2>

     <?php
       require dirname(__DIR__, 2) . '/components/order-detail-panel.php';
     ?>

    <?php if ($canCancel): ?>
        <form method="POST" action="/orders/<?= (int) ($order['id'] ?? 0) ?>/cancel" onsubmit="return confirm('Cancel this order?');">
            <?php if (isset($csrfField) && is_string($csrfField)): ?>
                <?= $csrfField ?>
            <?php endif; ?>
            <button type="submit" class="btn btn-outline-danger">Cancel order</button>
        </form>
    <?php endif; ?>
</section>
