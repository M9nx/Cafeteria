<?php

declare(strict_types=1);

use Cafeteria\Controllers\MediaController;
use Cafeteria\Core\Routing\Router;

/** @var Router $router */
$router->get(
    '/media/{kind}/{filename}',
    [MediaController::class, 'show'],
);

foreach (['auth.php', 'orders.php', 'admin.php', 'reports.php'] as $file) {
    $path = __DIR__ . '/' . $file;

    if (is_file($path)) {
        require $path;
    }
}
