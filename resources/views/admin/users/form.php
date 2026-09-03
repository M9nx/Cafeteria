<?php

declare(strict_types=1);

/** @var string $mode */
/** @var array<string, mixed>|null $user */
/** @var list<string> $errors */
/** @var string $csrfToken */
/** @var array<string, mixed> $old */

$isEdit = $mode === 'edit';
$user = $user ?? [];
$old = $old ?? [];
$errors = $errors ?? [];

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$name = $old['name'] ?? $user['name'] ?? '';
$email = $old['email'] ?? $user['email'] ?? '';
$role = $old['role'] ?? $user['role'] ?? 'USER';
$roomId = $old['room_id'] ?? $user['room_id'] ?? '';
$extension = $old['extension'] ?? $user['extension'] ?? '';

$action = $isEdit
    ? '/admin/users/' . (int) ($user['id'] ?? 0) . '/update'
    : '/admin/users';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">
            <?= $isEdit ? 'Edit user' : 'Create user' ?>
        </h1>
        <p class="text-body-secondary mb-0">
            <?= $isEdit
                ? 'Update account details, role, room assignment, or password.'
                : 'Add a cafeteria user or administrator. There is no public signup.'
            ?>
        </p>
    </div>

    <a href="/admin/users" class="btn btn-outline-secondary">
        Back to users
    </a>
</div>

<?php require dirname(__DIR__, 2) . '/components/form-errors.php'; ?>

<form
    method="POST"
    action="<?= $e($action) ?>"
    enctype="multipart/form-data"
    class="card"
>
    <div class="card-body">
        <input
            type="hidden"
            name="_csrf_token"
            value="<?= $e($csrfToken) ?>"
        >

        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    value="<?= $e($name) ?>"
                    required
                    maxlength="120"
                >
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    value="<?= $e($email) ?>"
                    required
                    maxlength="254"
                >
            </div>

            <div class="col-md-4">
                <label for="role" class="form-label">Role</label>
                <select id="role" name="role" class="form-select">
                    <option
                        value="USER"
                        <?= strtoupper((string) $role) === 'USER' ? 'selected' : '' ?>
                    >
                        User
                    </option>
                    <option
                        value="ADMIN"
                        <?= strtoupper((string) $role) === 'ADMIN' ? 'selected' : '' ?>
                    >
                        Admin
                    </option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="room_id" class="form-label">Room ID</label>
                <input
                    type="number"
                    id="room_id"
                    name="room_id"
                    class="form-control"
                    min="1"
                    value="<?= $e($roomId) ?>"
                >
            </div>

            <div class="col-md-4">
                <label for="extension" class="form-label">Extension</label>
                <input
                    type="text"
                    id="extension"
                    name="extension"
                    class="form-control"
                    value="<?= $e($extension) ?>"
                    maxlength="20"
                >
            </div>

            <div class="col-md-6">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    <?= $isEdit ? '' : 'required' ?>
                    minlength="8"
                    autocomplete="<?= $isEdit ? 'new-password' : 'new-password' ?>"
                >
                <?php if ($isEdit): ?>
                    <div class="form-text">
                        Leave empty to keep the current password.
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label for="image" class="form-label">Profile image</label>
                <input
                    type="file"
                    id="image"
                    name="image"
                    class="form-control"
                    accept="image/jpeg,image/png,image/webp"
                >
            </div>
        </div>
    </div>

    <div class="card-footer d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Update user' : 'Create user' ?>
        </button>
        <a href="/admin/users" class="btn btn-outline-secondary">
            Cancel
        </a>
    </div>
</form>
