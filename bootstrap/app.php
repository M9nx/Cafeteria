<?php

declare(strict_types=1);

use Cafeteria\Controllers\HealthController;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Routing\Router;

require __DIR__ . '/autoload.php';

$router = new Router();

$router->get('/health', [HealthController::class, 'show']);

$routesFile = dirname(__DIR__) . '/routes/web.php';

if (is_file($routesFile)) {
    require $routesFile;
}

return [
    'router' => $router,
    'request_factory' => static fn (): Request => Request::fromGlobals(),
];
