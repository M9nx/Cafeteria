<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $items */

$items = $items ?? [];

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<div class="order-detail-panel">
    <?php if ($items === []): ?>
        <p class="text-muted mb-0">No items in this order.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th scope="col">Product</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Line total</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= $e($item['product_name'] ?? $item['name'] ?? '') ?></td>
                            <td><?= $e($item['quantity'] ?? 0) ?></td>
                            <td><?= $e($item['line_total'] ?? $item['total'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>