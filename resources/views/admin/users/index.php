<?php

declare(strict_types=1);

/** @var array<string, mixed> $users */
/** @var array<string, string> $flash */
/** @var string $csrfToken */

$items = $users['items'] ?? [];
$total = (int) ($users['total'] ?? 0);
$page = max(1, (int) ($users['page'] ?? 1));
$perPage = max(1, (int) ($users['per_page'] ?? 15));
$totalPages = max(1, (int) ceil($total / $perPage));
$flash = $flash ?? [];

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Admin users</h1>
        <p class="text-body-secondary mb-0">
            Manage administrator accounts, rooms, and account status.
        </p>
    </div>

    <a href="/admin/users/create" class="btn btn-primary">
        Create admin user
    </a>
</div>

<?php foreach ($flash as $type => $message): ?>
    <?php
    $alertType = $type === 'error' ? 'danger' : (string) $type;
    $allowed = ['success', 'danger', 'warning', 'info'];
    $alertType = in_array($alertType, $allowed, true) ? $alertType : 'info';
    ?>
    <div class="alert alert-<?= $e($alertType) ?> alert-dismissible fade show" role="alert">
        <?= $e($message) ?>
        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Close message"
        ></button>
    </div>
<?php endforeach; ?>

<?php if ($items === []): ?>

    <div class="alert alert-info" role="status">
        No admin users found.
    </div>

<?php else: ?>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <caption class="visually-hidden">
                Administrator accounts
            </caption>
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Role</th>
                    <th scope="col">Room</th>
                    <th scope="col">Extension</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $user): ?>
                    <tr>
                        <td><?= (int) ($user['id'] ?? 0) ?></td>
                        <td><?= $e($user['name'] ?? '') ?></td>
                        <td><?= $e($user['email'] ?? '') ?></td>
                        <td><?= $e($user['role'] ?? '') ?></td>
                        <td>
                            <?= $user['room_id'] !== null
                                ? (int) $user['room_id']
                                : '—'
                            ?>
                        </td>
                        <td><?= $e($user['extension'] ?? '—') ?></td>
                        <td>
                            <?php if ((int) ($user['is_active'] ?? 0) === 1): ?>
                                <span class="badge text-bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                <a
                                    href="/admin/users/<?= (int) ($user['id'] ?? 0) ?>/edit"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    Edit
                                </a>

                                <?php if ((int) ($user['is_active'] ?? 0) === 1): ?>
                                    <form
                                        method="POST"
                                        action="/admin/users/<?= (int) ($user['id'] ?? 0) ?>/deactivate"
                                        onsubmit="return confirm('Deactivate this admin user?');"
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

    <?php if ($totalPages > 1): ?>
        <nav aria-label="Admin user pagination" class="mt-3">
            <ul class="pagination">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <?php if ($page > 1): ?>
                        <a class="page-link" href="/admin/users?page=<?= $page - 1 ?>">
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
                            <a class="page-link" href="/admin/users?page=<?= $currentPage ?>">
                                <?= $currentPage ?>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <?php if ($page < $totalPages): ?>
                        <a class="page-link" href="/admin/users?page=<?= $page + 1 ?>">
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
