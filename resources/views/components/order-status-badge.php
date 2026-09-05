<?php

declare(strict_types=1);

/** @var string $status */

$status = strtoupper(trim($status ?? ''));

$labels = [
    'PROCESSING' => 'Processing',
    'OUT_FOR_DELIVERY' => 'Out for delivery',
    'DONE' => 'Done',
    'CANCELLED' => 'Cancelled',
];

$label = $labels[$status] ?? ucfirst(strtolower(str_replace('_', ' ', $status)));

$classes = [
    'PROCESSING' => 'status-processing',
    'OUT_FOR_DELIVERY' => 'status-delivery',
    'DONE' => 'status-done',
    'CANCELLED' => 'status-cancelled',
];

$class = $classes[$status] ?? 'text-bg-secondary';
?>

<span class="badge <?= htmlspecialchars($class, ENT_QUOTES, 'UTF-8') ?>">
    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
</span>
