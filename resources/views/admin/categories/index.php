<?php

declare(strict_types=1);

/** @var array<string, mixed> $categories */
/** @var array<string, string> $flash */
/** @var string $csrfToken */

$items = $categories['items'] ?? [];
$total = (int) ($categories['total'] ?? 0);
$page = max(1, (int) ($categories['page'] ?? 1));
$perPage = max(1, (int) ($categories['per_page'] ?? 15));
$totalPages = max(1, (int) ceil($total / $perPage));

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<div class="page-heading">
    <div>
        <h1 class="h3">Categories</h1>
        <p>Organize catalogue products into categories.</p>
    </div>

    <a href="/admin/categories/create" class="btn btn-primary">
        Create category
    </a>
</div>

<?php if ($items === []): ?>

    <div class="alert alert-info" role="status">
        No categories found.
    </div>

<?php else: ?>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <caption class="visually-hidden">Product categories</caption>
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $category): ?>
                    <tr>
                        <td><?= (int) ($category['id'] ?? 0) ?></td>
                        <td><?= $e($category['name'] ?? '') ?></td>
                        <td>
                            <?php if ((int) ($category['is_active'] ?? 0) === 1): ?>
                                <span class="badge text-bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                <a
                                    href="/admin/categories/<?= (int) ($category['id'] ?? 0) ?>/edit"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    Edit
                                </a>

                                <?php if ((int) ($category['is_active'] ?? 0) === 1): ?>
                                    <form
                                        method="POST"
                                        action="/admin/categories/<?= (int) ($category['id'] ?? 0) ?>/deactivate"
                                        data-confirm="Deactivate this category?"
                                        data-confirm-title="Deactivate category"
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
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <nav aria-label="Category pagination" class="mt-3 admin-pagination">
        <ul class="pagination mb-0">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <?php if ($page > 1): ?>
                    <a class="page-link" href="/admin/categories?page=<?= $page - 1 ?>">Previous</a>
                <?php else: ?>
                    <span class="page-link">Previous</span>
                <?php endif; ?>
            </li>
            <li class="page-item disabled">
                <span class="page-link">Page <?= $page ?> of <?= $totalPages ?></span>
            </li>
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <?php if ($page < $totalPages): ?>
                    <a class="page-link" href="/admin/categories?page=<?= $page + 1 ?>">Next</a>
                <?php else: ?>
                    <span class="page-link">Next</span>
                <?php endif; ?>
            </li>
        </ul>
    </nav>

<?php endif; ?>
