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
    '/orders',
    [OrderController::class, 'index'],
    [$authMiddleware]
);

$router->get(
    '/orders/new',
    [OrderController::class, 'create'],
    [$authMiddleware]
);

$router->post(
    '/orders',
    [OrderController::class, 'store'],
    [$authMiddleware]
);

$router->get(
    '/orders/{id}',
    [OrderController::class, 'show'],
    [$authMiddleware]
);

$router->post(
    '/orders/{id}/cancel',
    [OrderController::class, 'cancel'],
    [$authMiddleware]
);
