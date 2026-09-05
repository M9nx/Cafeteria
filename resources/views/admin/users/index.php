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

<div class="page-heading">
    <div>
        <h1 class="h3">Users</h1>
        <p>
            Manage cafeteria accounts. There is no public signup, so add both admins and users here.
        </p>
    </div>

    <a href="/admin/users/create" class="btn btn-primary">
        Create user
    </a>
</div>

<?php if ($items === []): ?>

    <div class="alert alert-info" role="status">
        No users found.
    </div>

<?php else: ?>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <caption class="visually-hidden">
                Cafeteria user accounts
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
                        <td>
                            <?php
                            $role = strtoupper((string) ($user['role'] ?? ''));
                            $roleBadge = $role === 'ADMIN'
                                ? 'text-bg-primary'
                                : 'text-bg-secondary';
                            $roleLabel = $role === 'ADMIN' ? 'Admin' : 'User';
                            ?>
                            <span class="badge <?= $roleBadge ?>">
                                <?= $e($roleLabel) ?>
                            </span>
                        </td>
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
                                        data-confirm="Deactivate this user? They will no longer be able to sign in."
                                        data-confirm-title="Deactivate user"
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

    <nav aria-label="User pagination" class="mt-3 admin-pagination">
        <ul class="pagination mb-0">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <?php if ($page > 1): ?>
                    <a class="page-link" href="/admin/users?page=<?= $page - 1 ?>">
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
