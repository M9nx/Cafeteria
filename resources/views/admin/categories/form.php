<?php

declare(strict_types=1);

/** @var string $mode */
/** @var array<string, mixed>|null $category */
/** @var list<string> $errors */
/** @var string $csrfToken */

$isEdit = $mode === 'edit';

$name = $category['name'] ?? ($oldName ?? '');
$isActive = isset($category['is_active'])
    ? (bool) $category['is_active']
    : true;

$action = $isEdit
    ? '/admin/categories/' . (int) $category['id'] . '/update'
    : '/admin/categories';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>
        <?= $isEdit ? 'Edit Category' : 'Create Category' ?>
    </title>
</head>

<body>

<h1>
    <?= $isEdit ? 'Edit Category' : 'Create Category' ?>
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

<form method="POST" action="<?= htmlspecialchars(
    $action,
    ENT_QUOTES,
    'UTF-8'
) ?>">

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

    <?php if ($isEdit): ?>

        <div>
            <label>
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    <?= $isActive ? 'checked' : '' ?>
                >
                Active
            </label>
        </div>

    <?php endif; ?>

    <button type="submit">
        <?= $isEdit ? 'Update' : 'Create' ?>
    </button>

    <a href="/admin/categories">
        Cancel
    </a>

</form>

</body>
</html>