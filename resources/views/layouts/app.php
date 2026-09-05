<?php

declare(strict_types=1);

/** @var string $title */
/** @var string $content */
/** @var object|null $currentUser Optional user object with isAdmin(): bool */
/** @var array{type?: string, message?: string}|null $flash */

$pageTitle = htmlspecialchars($title ?? 'Fondo2na', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $pageTitle ?></title>
    <?php require dirname(__DIR__) . '/components/brand-icons.php'; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >
    <link href="/assets/css/app.css" rel="stylesheet">
    <link href="/assets/css/notifications.css" rel="stylesheet">
</head>
<body class="app-body d-flex flex-column min-vh-100">
    <?php require dirname(__DIR__) . '/components/navbar.php'; ?>

    <main id="main-content" class="app-main flex-grow-1 py-4" tabindex="-1">
        <div class="container">
            <?php require dirname(__DIR__) . '/components/alerts.php'; ?>
            <?= $content ?>
        </div>
    </main>

    <footer class="app-footer py-3 mt-auto">
        <div class="container text-center small">
            © Fondo2na Cafeteria Management System. All rights reserved.
        </div>
    </footer>

    <?php require dirname(__DIR__) . '/components/confirm-modal.php'; ?>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"
    ></script>
    <script src="/assets/js/app.js" defer></script>
</body>
</html>
