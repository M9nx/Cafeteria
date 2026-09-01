<?php

declare(strict_types=1);

use Cafeteria\Controllers\Auth\ForgotPasswordController;
use Cafeteria\Controllers\Auth\LoginController;
use Cafeteria\Controllers\Auth\LogoutController;
use Cafeteria\Controllers\Auth\ResetPasswordController;
use Cafeteria\Core\Routing\Router;

/** @var Router $router */

$router->get(
    '/login',
    [LoginController::class, 'show'],
    [$guestMiddleware],
);

$router->post(
    '/login',
    [LoginController::class, 'login'],
    [$guestMiddleware],
);

$router->post(
    '/logout',
    [LogoutController::class, 'logout'],
    [$authMiddleware],
);

$router->get(
    '/forgot-password',
    [ForgotPasswordController::class, 'show'],
    [$guestMiddleware],
);

$router->post(
    '/forgot-password',
    [ForgotPasswordController::class, 'requestReset'],
    [$guestMiddleware],
);

$router->get(
    '/reset-password',
    [ResetPasswordController::class, 'show'],
    [$guestMiddleware],
);

$router->post(
    '/reset-password',
    [ResetPasswordController::class, 'reset'],
    [$guestMiddleware],
);