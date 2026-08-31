<?php

declare(strict_types=1);

use Cafeteria\Controllers\HealthController;
use Cafeteria\Core\Auth\AdminMiddleware;
use Cafeteria\Core\Auth\AuthMiddleware;
use Cafeteria\Core\Auth\CsrfTokenManager;
use Cafeteria\Core\Auth\GuestMiddleware;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Routing\Router;
use Cafeteria\Core\Session\FlashBag;
use Cafeteria\Core\Session\SessionManager;
use Cafeteria\Policies\AdminPolicy;
use Cafeteria\Policies\OrderPolicy;

require __DIR__ . '/autoload.php';

$appConfig = require dirname(__DIR__) . '/config/app.php';
$sessionConfig = require dirname(__DIR__) . '/config/session.php';

$session = new SessionManager($sessionConfig);
$session->start();

$flash = new FlashBag($session);
$csrf = new CsrfTokenManager($session);

$authMiddleware = new AuthMiddleware($session);
$adminMiddleware = new AdminMiddleware($session);
$guestMiddleware = new GuestMiddleware($session);

$adminPolicy = new AdminPolicy();
$orderPolicy = new OrderPolicy();

$router = new Router();

$router->get('/health', [HealthController::class, 'show']);

$routesFile = dirname(__DIR__) . '/routes/web.php';

if (is_file($routesFile)) {
    require $routesFile;
}

return [
    'config' => [
        'app' => $appConfig,
        'session' => $sessionConfig,
    ],
    'session' => $session,
    'flash' => $flash,
    'csrf' => $csrf,
    'middleware' => [
        'auth' => $authMiddleware,
        'admin' => $adminMiddleware,
        'guest' => $guestMiddleware,
    ],
    'policies' => [
        'admin' => $adminPolicy,
        'order' => $orderPolicy,
    ],
    'router' => $router,
    'request_factory' => static fn (): Request => Request::fromGlobals(),
];
