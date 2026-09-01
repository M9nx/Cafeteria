<?php

declare(strict_types=1);

/** @var array<string, mixed> $categories */
/** @var array<string, string> $flash */
/** @var string $csrfToken */

$items = $categories['items'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Categories</title>
</head>

<body>

<h1>Admin Categories</h1>

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
    <a href="/admin/categories/create">
        Create Category
    </a>
</p>

<?php if ($items === []): ?>

    <p>No categories found.</p>

<?php else: ?>

<table border="1" cellpadding="8">

    <thead>

    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

    </thead>

    <tbody>

    <?php foreach ($items as $category): ?>

        <tr>

            <td>
                <?= (int) $category['id'] ?>
            </td>

            <td>
                <?= htmlspecialchars(
                    (string) $category['name'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </td>

            <td>
                <?= (int) $category['is_active'] === 1
                    ? 'Active'
                    : 'Inactive'
                ?>
            </td>

            <td>

                <a href="/admin/categories/<?= (int) $category['id'] ?>/edit">
                    Edit
                </a>

                <?php if ((int) $category['is_active'] === 1): ?>

                    <form
                        method="POST"
                        action="/admin/categories/<?= (int) $category['id'] ?>/deactivate"
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
                            onclick="return confirm('Are you sure you want to deactivate this category?')"
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
