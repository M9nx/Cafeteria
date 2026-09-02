<?php

declare(strict_types=1);
/**
 * Expected variables:
 *
 * @var array{
 *     items: array<int, array<string, mixed>>,
 *     total: int,
 *     page: int,
 *     per_page: int
 * } $products
 * @var string $csrfToken
 * @var array<int, array{type?: string, message?: string}> $flash
 */

$items = $products['items'] ?? [];
$total = (int) ($products['total'] ?? 0);
$page = max(1, (int) ($products['page'] ?? 1));
$perPage = max(1, (int) ($products['per_page'] ?? 15));

$totalPages = max(1, (int) ceil($total / $perPage));

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Products</h1>

        <a
            href="/admin/products/create"
            class="btn btn-primary"
        >
            Create Product
        </a>
    </div>

    <?php foreach ($flash as $message): ?>
        <?php
        $type = $message['type'] ?? 'info';
        $text = $message['message'] ?? '';
        ?>
        <div
            class="alert alert-<?= $e($type) ?>"
            role="alert"
        >
            <?= $e($text) ?>
        </div>
    <?php endforeach; ?>

    <?php if ($items === []): ?>

        <div class="alert alert-info" role="status">
            No products found.
        </div>

    <?php else: ?>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Category</th>
                        <th scope="col">Price</th>
                        <th scope="col">Availability</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($items as $product): ?>
                        <tr>
                            <td>
                                <?= $e($product['name'] ?? '') ?>
                            </td>

                            <td>
                                <?= $e($product['category_name'] ?? $product['category_id'] ?? '') ?>
                            </td>

                            <td>
                                <?= $e($product['price'] ?? '') ?>
                            </td>

                            <td>
                                <?php if (!empty($product['is_available'])): ?>
                                    <span class="badge text-bg-success">
                                        Available
                                    </span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">
                                        Unavailable
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex gap-2">

                                    <a
                                        href="/admin/products/<?= $e($product['id'] ?? '') ?>/edit"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="/admin/products/<?= $e($product['id'] ?? '') ?>/deactivate"
                                        onsubmit="return confirm('Deactivate this product?');"
                                    >
                                        <input
                                            type="hidden"
                                            name="_token"
                                            value="<?= $e($csrfToken) ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                        >
                                            Deactivate
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav aria-label="Product pagination">
                <ul class="pagination">

                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <?php if ($page > 1): ?>
                            <a
                                class="page-link"
                                href="/admin/products?page=<?= $page - 1 ?>"
                            >
                                Previous
                            </a>
                        <?php else: ?>
                            <span class="page-link">Previous</span>
                        <?php endif; ?>
                    </li>

                    <?php for ($currentPage = 1; $currentPage <= $totalPages; $currentPage++): ?>
                        <li class="page-item <?= $currentPage === $page ? 'active' : '' ?>">
                            <?php if ($currentPage === $page): ?>
                                <span class="page-link" aria-current="page">
                                    <?= $currentPage ?>
                                </span>
                            <?php else: ?>
                                <a
                                    class="page-link"
                                    href="/admin/products?page=<?= $currentPage ?>"
                                >
                                    <?= $currentPage ?>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <?php if ($page < $totalPages): ?>
                            <a
                                class="page-link"
                                href="/admin/products?page=<?= $page + 1 ?>"
                            >
                                Next
                            </a>
                        <?php else: ?>
                            <span class="page-link">Next</span>
                        <?php endif; ?>
                    </li>

                </ul>
            </nav>
        <?php endif; ?>

    <?php endif; ?>

</div>
