<?php

declare(strict_types=1);

use Cafeteria\Core\Routing\Router;

/** @var Router $router */
foreach (['auth.php', 'orders.php', 'admin.php', 'reports.php'] as $file) {
    $path = __DIR__ . '/' . $file;

    if (is_file($path)) {
        require $path;
    }
}
