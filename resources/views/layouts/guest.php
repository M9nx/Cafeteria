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
</head>
<body class="guest-body">
    <main id="main-content" class="guest-main d-flex align-items-center min-vh-100 py-4" tabindex="-1">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-8 col-lg-5">
                    <div class="card guest-card shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <?php require dirname(__DIR__) . '/components/alerts.php'; ?>
                            <?= $content ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"
    ></script>
    <script src="/assets/js/app.js" defer></script>
</body>
</html>
