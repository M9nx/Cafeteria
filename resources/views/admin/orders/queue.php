<?php

declare(strict_types=1);

use Cafeteria\Domain\Orders\OrderTransitionMatrix;

/** @var array<string, mixed> $queue */
/** @var array<string, string>|null $flashMessages */

$items = $queue['items'] ?? [];

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<section class="order-queue" aria-labelledby="queue-heading">
    <h1 id="queue-heading" class="h3 mb-4">Current order queue</h1>

    <?php require dirname(__DIR__, 2) . '/components/order-assets.php'; ?>
    <?php require dirname(__DIR__, 2) . '/components/admin-flash.php'; ?>

    <?php if ($items === []): ?>
        <div class="alert alert-info" role="status">No active orders in the queue.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Customer</th>
                        <th scope="col">Room</th>
                        <th scope="col">Status</th>
                        <th scope="col">Total</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $order): ?>
                        <?php
                        $orderId = (int) ($order['id'] ?? 0);
                        $status = (string) ($order['status'] ?? '');
                        $nextStatuses = OrderTransitionMatrix::allowedNextStatuses($status);
                        ?>
                        <tr>
                            <td><?= $orderId ?></td>
                            <td><?= $e($order['user_name'] ?? '') ?></td>
                            <td><?= $e($order['room_name'] ?? '') ?></td>
                            <td>
                                <?php require dirname(__DIR__, 2) . '/components/order-status-badge.php'; ?>
                            </td>
                            <td><?= $e($order['total_amount'] ?? '') ?></td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($nextStatuses as $nextStatus): ?>
                                        <form
                                            method="POST"
                                            action="/admin/orders/<?= $orderId ?>/status"
                                            onsubmit="return confirm('Move order to <?= $e($nextStatus) ?>?');"
                                        >
                                            <?php if (isset($csrfField) && is_string($csrfField)): ?>
                                                <?= $csrfField ?>
                                            <?php endif; ?>
                                            <input type="hidden" name="status" value="<?= $e($nextStatus) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                                Mark <?= $e($nextStatus) ?>
                                            </button>
                                        </form>
                                    <?php endforeach; ?>

                                    <?php if ($status === 'PROCESSING'): ?>
                                        <form
                                            method="POST"
                                            action="/orders/<?= $orderId ?>/cancel"
                                            onsubmit="return confirm('Cancel this order?');"
                                        >
                                            <?php if (isset($csrfField) && is_string($csrfField)): ?>
                                                <?= $csrfField ?>
                                            <?php endif; ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                Cancel
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
