<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $items */

$items = $items ?? [];

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<div class="order-detail-panel">
    <?php if ($items === []): ?>
        <div class="alert alert-info mb-0" role="status">No line items found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Product</th>
                        <th scope="col">Unit price</th>
                        <th scope="col">Qty</th>
                        <th scope="col">Line total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <?php
                        $itemName = (string) ($item['product_name_snapshot'] ?? '');
                        $itemImage = \Cafeteria\Support\PublicFileUrl::fromStoredPath(
                            isset($item['product_image_path'])
                                ? (string) $item['product_image_path']
                                : null
                        );
                        ?>
                        <tr>
                            <td>
                                <div class="order-line-product">
                                    <img
                                        class="admin-thumb"
                                        src="<?= $e($itemImage) ?>"
                                        alt=""
                                        width="40"
                                        height="40"
                                        loading="lazy"
                                    >
                                    <span><?= $e($itemName) ?></span>
                                </div>
                            </td>
                            <td><?= $e($item['unit_price_snapshot'] ?? '') ?></td>
                            <td><?= (int) ($item['quantity'] ?? 0) ?></td>
                            <td><?= $e($item['line_total'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
