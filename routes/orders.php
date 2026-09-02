<?php
/** @var \Cafeteria\Core\Routing\Router $router */
/** @var \Cafeteria\Core\Auth\AuthMiddleware $authMiddleware */
declare(strict_types=1);

use Cafeteria\Controllers\User\CatalogController;
use Cafeteria\Controllers\User\OrderController;

$router->get(
    '/',
    [CatalogController::class, 'index'],
    [$authMiddleware]
);

$router->get(
    '/orders/create',
    [OrderController::class, 'create'],
    [$authMiddleware]
);

$router->post(
    '/orders',
    [OrderController::class, 'store'],
    [$authMiddleware]
);