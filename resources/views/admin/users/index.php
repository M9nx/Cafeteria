<?php

declare(strict_types=1);

/** @var array<string, mixed> $users */
/** @var array<string, string> $flash */
/** @var string $csrfToken */

$items = $users['items'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Users</title>
</head>

<body>

<h1>Admin Users</h1>

<?php foreach ($flash as $type => $message): ?>

    <div>
        <?= htmlspecialchars(
            $message,
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </div>

<?php endforeach; ?>

<p>
    <a href="/admin/users/create">
        Create Admin User
    </a>
</p>

<?php if ($items === []): ?>

    <p>No admin users found.</p>

<?php else: ?>

<table border="1" cellpadding="8">

    <thead>

    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Room</th>
        <th>Extension</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

    </thead>

    <tbody>

    <?php foreach ($items as $user): ?>

        <tr>

            <td>
                <?= (int) $user['id'] ?>
            </td>

            <td>
                <?= htmlspecialchars(
                    (string) $user['name'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </td>

            <td>
                <?= htmlspecialchars(
                    (string) $user['email'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </td>

            <td>
                <?= htmlspecialchars(
                    (string) $user['role'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </td>

            <td>
                <?= $user['room_id'] !== null
                    ? (int) $user['room_id']
                    : '-'
                ?>
            </td>

            <td>
                <?= htmlspecialchars(
                    (string) ($user['extension'] ?? '-'),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </td>

            <td>
                <?= (int) $user['is_active'] === 1
                    ? 'Active'
                    : 'Inactive'
                ?>
            </td>

            <td>

                <a href="/admin/users/<?= (int) $user['id'] ?>/edit">
                    Edit
                </a>

                <?php if ((int) $user['is_active'] === 1): ?>

                    <form
                        method="POST"
                        action="/admin/users/<?= (int) $user['id'] ?>/deactivate"
                        style="display:inline"
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

                        <button
                            type="submit"
                            onclick="return confirm('Are you sure you want to deactivate this admin user?')"
                        >
                            Deactivate
                        </button>

                    </form>

                <?php endif; ?>

            </td>

        </tr>

    <?php endforeach; ?>

    </tbody>

</table>

<?php endif; ?>

<?php
require dirname(__DIR__, 2)
    . '/components/pagination.php';
?>

</body>
</html>