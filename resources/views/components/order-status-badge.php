<?php

declare(strict_types=1);

/** @var string $status */

$status = strtoupper(trim($status ?? ''));

$labels = [
    'PENDING' => 'Pending',
    'PROCESSING' => 'Processing',
    'OUT_FOR_DELIVERY' => 'Out for delivery',
    'Done' => 'Done',
    'CANCELLED' => 'Cancelled',
];

$label = $labels[$status] ?? ucfirst(strtolower(str_replace('_', ' ', $status)));

$classes = [
    'PENDING' => 'bg-warning text-dark',
    'PROCESSING' => 'bg-info text-dark',
    'OUT_FOR_DELIVERY' => 'bg-primary',
    'Done' => 'bg-success',
    'CANCELLED' => 'bg-danger',
];

$class = $classes[$status] ?? 'bg-secondary';
?>

<span class="badge <?= htmlspecialchars($class, ENT_QUOTES, 'UTF-8') ?>">
    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
</span>