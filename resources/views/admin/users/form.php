<?php

declare(strict_types=1);

/** @var string $mode */
/** @var array<string, mixed>|null $user */
/** @var list<string> $errors */
/** @var string $csrfToken */

$isEdit = $mode === 'edit';

$user ??= [];

$name = $user['name'] ?? '';
$email = $user['email'] ?? '';
$role = $user['role'] ?? 'ADMIN';
$roomId = $user['room_id'] ?? '';
$extension = $user['extension'] ?? '';

$action = $isEdit
    ? '/admin/users/' . (int) $user['id'] . '/update'
    : '/admin/users';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>
        <?= $isEdit ? 'Edit Admin User' : 'Create Admin User' ?>
    </title>
</head>

<body>

<h1>
    <?= $isEdit ? 'Edit Admin User' : 'Create Admin User' ?>
</h1>

<?php if ($errors !== []): ?>

    <div>

        <?php foreach ($errors as $error): ?>

            <p>
                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

        <?php endforeach; ?>

    </div>

<?php endif; ?>

<form
    method="POST"
    action="<?= htmlspecialchars(
        $action,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    enctype="multipart/form-data"
>

    <input
        type="hidden"
        name="_csrf_token"
        value="<?= htmlspecialchars(
            $csrfToken,
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
    >

    <div>
        <label for="name">Name</label>

        <input
            type="text"
            id="name"
            name="name"
            value="<?= htmlspecialchars(
                (string) $name,
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
            required
            maxlength="120"
        >
    </div>

    <div>
        <label for="email">Email</label>

        <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars(
                (string) $email,
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
            required
            maxlength="254"
        >
    </div>

    <div>
        <label for="role">Role</label>

        <select id="role" name="role">

            <option
                value="ADMIN"
                <?= strtoupper((string) $role) === 'ADMIN'
                    ? 'selected'
                    : '' ?>
            >
                ADMIN
            </option>

            <option
                value="USER"
                <?= strtoupper((string) $role) === 'USER'
                    ? 'selected'
                    : '' ?>
            >
                USER
            </option>

        </select>
    </div>

    <div>
        <label for="room_id">Room ID</label>

        <input
            type="number"
            id="room_id"
            name="room_id"
            value="<?= htmlspecialchars(
                (string) $roomId,
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >
    </div>

    <div>
        <label for="extension">Extension</label>

        <input
            type="text"
            id="extension"
            name="extension"
            value="<?= htmlspecialchars(
                (string) $extension,
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
            maxlength="20"
        >
    </div>

    <div>
        <label for="password">
            Password
            <?= $isEdit ? '(leave empty to keep current password)' : '' ?>
        </label>

        <input
            type="password"
            id="password"
            name="password"
            <?= $isEdit ? '' : 'required' ?>
            minlength="8"
        >
    </div>

    <div>
        <label for="image">Profile Image</label>

        <input
            type="file"
            id="image"
            name="image"
            accept="image/jpeg,image/png,image/webp"
        >
    </div>

    <button type="submit">
        <?= $isEdit ? 'Update' : 'Create' ?>
    </button>

    <a href="/admin/users">
        Cancel
    </a>

</form>

</body>
</html>