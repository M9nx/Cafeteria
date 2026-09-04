<?php

declare(strict_types=1);

/** @var string $title */
/** @var string $content */
/** @var array{type?: string, message?: string}|null $flash */

$pageTitle = htmlspecialchars($title ?? 'Cafeteria', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $pageTitle ?></title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >
    <link href="/assets/css/app.css" rel="stylesheet">
    <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&display=swap"
    rel="stylesheet"
     >
</head>
<body class="guest-body">
    <main id="main-content" class="guest-main" tabindex="-1">
        <div class="guest-container">
            <div class="guest-card">
                <?php require dirname(__DIR__) . '/components/alerts.php'; ?>
                <?= $content ?>
            </div>
        </div>
    </main>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMkmK1eN7N6jIeHz"
        crossorigin="anonymous"
    ></script>
    <script src="/assets/js/app.js" defer></script>
</body>
</html>