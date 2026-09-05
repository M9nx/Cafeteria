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

$flash = $flash ?? [];
?>

<div class="page-heading">
    <div>
        <h1 class="h3">Products</h1>
        <p>
            Manage catalog items, prices, and availability.
        </p>
    </div>

    <a href="/admin/products/create" class="btn btn-primary">
        Create product
    </a>
</div>

    <?php if ($items === []): ?>

        <div class="alert alert-info" role="status">
            No products found.
        </div>

    <?php else: ?>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th scope="col">Image</th>
                        <th scope="col">Name</th>
                        <th scope="col">Category</th>
                        <th scope="col">Price</th>
                        <th scope="col">Availability</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($items as $product): ?>
                        <?php
                        $productImage = \Cafeteria\Support\PublicFileUrl::fromStoredPath(
                            isset($product['image_path']) ? (string) $product['image_path'] : null
                        );
                        ?>
                        <tr>
                            <td>
                                <img
                                    class="admin-thumb"
                                    src="<?= $e($productImage) ?>"
                                    alt=""
                                    width="48"
                                    height="48"
                                    loading="lazy"
                                >
                            </td>
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
                                        data-confirm="Deactivate this product? It will leave the catalogue."
                                        data-confirm-title="Deactivate product"
                                        data-confirm-label="Deactivate"
                                        data-confirm-tone="danger"
                                    >
                                        <input
                                            type="hidden"
                                            name="_csrf_token"
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

        <nav aria-label="Product pagination" class="mt-3 admin-pagination">
            <ul class="pagination mb-0">
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

                <li class="page-item disabled">
                    <span class="page-link">Page <?= $page ?> of <?= $totalPages ?></span>
                </li>

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
