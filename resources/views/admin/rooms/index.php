<?php

declare(strict_types=1);

/** @var array<string, mixed> $rooms */
/** @var array<string, string> $flash */
/** @var string $csrfToken */

$items = $rooms['items'] ?? [];
$total = (int) ($rooms['total'] ?? 0);
$page = max(1, (int) ($rooms['page'] ?? 1));
$perPage = max(1, (int) ($rooms['per_page'] ?? 15));
$totalPages = max(1, (int) ceil($total / $perPage));

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<div class="page-heading">
    <div>
        <h1 class="h3">Rooms</h1>
        <p>Manage delivery rooms and desk locations.</p>
    </div>

    <a href="/admin/rooms/create" class="btn btn-primary">
        Create room
    </a>
</div>

<?php if ($items === []): ?>

    <div class="alert alert-info" role="status">
        No rooms found.
    </div>

<?php else: ?>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <caption class="visually-hidden">Cafeteria rooms</caption>
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $room): ?>
                    <tr>
                        <td><?= (int) ($room['id'] ?? 0) ?></td>
                        <td><?= $e($room['name'] ?? '') ?></td>
                        <td>
                            <?php if ((int) ($room['is_active'] ?? 0) === 1): ?>
                                <span class="badge text-bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                <a
                                    href="/admin/rooms/<?= (int) ($room['id'] ?? 0) ?>/edit"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    Edit
                                </a>

                                <?php if ((int) ($room['is_active'] ?? 0) === 1): ?>
                                    <form
                                        method="POST"
                                        action="/admin/rooms/<?= (int) ($room['id'] ?? 0) ?>/deactivate"
                                        data-confirm="Deactivate this room?"
                                        data-confirm-title="Deactivate room"
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

    <nav aria-label="Room pagination" class="mt-3 admin-pagination">
        <ul class="pagination mb-0">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <?php if ($page > 1): ?>
                    <a class="page-link" href="/admin/rooms?page=<?= $page - 1 ?>">Previous</a>
                <?php else: ?>
                    <span class="page-link">Previous</span>
                <?php endif; ?>
            </li>
            <li class="page-item disabled">
                <span class="page-link">Page <?= $page ?> of <?= $totalPages ?></span>
            </li>
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <?php if ($page < $totalPages): ?>
                    <a class="page-link" href="/admin/rooms?page=<?= $page + 1 ?>">Next</a>
                <?php else: ?>
                    <span class="page-link">Next</span>
                <?php endif; ?>
            </li>
        </ul>
    </nav>

<?php endif; ?>
