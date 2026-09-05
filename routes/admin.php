<?php

declare(strict_types=1);

/** @var \Cafeteria\Core\Routing\Router $router */
/** @var \Cafeteria\Core\Auth\AdminMiddleware $adminMiddleware */

use Cafeteria\Controllers\Admin\AdminOrderController;
use Cafeteria\Controllers\Admin\CategoryController;
use Cafeteria\Controllers\Admin\FulfillmentController;
use Cafeteria\Controllers\Admin\ProductController;
use Cafeteria\Controllers\Admin\ReportController;
use Cafeteria\Controllers\Admin\RoomController;
use Cafeteria\Controllers\Admin\UserController;

$router->get(
    '/admin/categories',
    [CategoryController::class, 'index'],
    [$adminMiddleware]
);

$router->get(
    '/admin/categories/create',
    [CategoryController::class, 'create'],
    [$adminMiddleware]
);

$router->post(
    '/admin/categories',
    [CategoryController::class, 'store'],
    [$adminMiddleware]
);

$router->get(
    '/admin/categories/{id}/edit',
    [CategoryController::class, 'edit'],
    [$adminMiddleware]
);

$router->post(
    '/admin/categories/{id}/update',
    [CategoryController::class, 'update'],
    [$adminMiddleware]
);

$router->post(
    '/admin/categories/{id}/deactivate',
    [CategoryController::class, 'deactivate'],
    [$adminMiddleware]
);

$router->get(
    '/admin/rooms',
    [RoomController::class, 'index'],
    [$adminMiddleware]
);

$router->get(
    '/admin/rooms/create',
    [RoomController::class, 'create'],
    [$adminMiddleware]
);

$router->post(
    '/admin/rooms',
    [RoomController::class, 'store'],
    [$adminMiddleware]
);

$router->get(
    '/admin/rooms/{id}/edit',
    [RoomController::class, 'edit'],
    [$adminMiddleware]
);

$router->post(
    '/admin/rooms/{id}/update',
    [RoomController::class, 'update'],
    [$adminMiddleware]
);

$router->post(
    '/admin/rooms/{id}/deactivate',
    [RoomController::class, 'deactivate'],
    [$adminMiddleware]
);

$router->get(
    '/admin/users',
    [UserController::class, 'index'],
    [$adminMiddleware]
);

$router->get(
    '/admin/users/create',
    [UserController::class, 'create'],
    [$adminMiddleware]
);

$router->post(
    '/admin/users',
    [UserController::class, 'store'],
    [$adminMiddleware]
);

$router->get(
    '/admin/users/{id}/edit',
    [UserController::class, 'edit'],
    [$adminMiddleware]
);

$router->post(
    '/admin/users/{id}/update',
    [UserController::class, 'update'],
    [$adminMiddleware]
);

$router->post(
    '/admin/users/{id}/deactivate',
    [UserController::class, 'deactivate'],
    [$adminMiddleware]
);

$router->get(
    '/admin/products',
    [ProductController::class, 'index'],
    [$adminMiddleware]
);

$router->get(
    '/admin/products/create',
    [ProductController::class, 'create'],
    [$adminMiddleware]
);

$router->post(
    '/admin/products',
    [ProductController::class, 'store'],
    [$adminMiddleware]
);

$router->get(
    '/admin/products/{id}/edit',
    [ProductController::class, 'edit'],
    [$adminMiddleware]
);

$router->post(
    '/admin/products/{id}/update',
    [ProductController::class, 'update'],
    [$adminMiddleware]
);

$router->post(
    '/admin/products/{id}/deactivate',
    [ProductController::class, 'deactivate'],
    [$adminMiddleware]
);

/**
 *--------------------------------------------------------------------------
 * Admin orders
 *--------------------------------------------------------------------------
 */

$router->get(
    '/admin/orders',
    [FulfillmentController::class, 'current'],
    [$adminMiddleware]
);

$router->get(
    '/admin/orders/create',
    [AdminOrderController::class, 'create'],
    [$adminMiddleware]
);

$router->post(
    '/admin/orders',
    [AdminOrderController::class, 'store'],
    [$adminMiddleware]
);

$router->get(
    '/admin/orders/current',
    [FulfillmentController::class, 'current'],
    [$adminMiddleware]
);

$router->post(
    '/admin/orders/{id}/status',
    [FulfillmentController::class, 'updateStatus'],
    [$adminMiddleware]
);

/**
 *--------------------------------------------------------------------------
 * Admin reports
 *--------------------------------------------------------------------------
 */

$router->get(
    '/admin/checks/export',
    [ReportController::class, 'export'],
    [$adminMiddleware]
);

$router->get(
    '/admin/checks/users/{id}',
    [ReportController::class, 'userDrillDown'],
    [$adminMiddleware]
);

$router->get(
    '/admin/checks',
    [ReportController::class, 'index'],
    [$adminMiddleware]
);

