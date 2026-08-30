<?php

declare(strict_types=1);

$app = require dirname(__DIR__) . '/bootstrap/app.php';

$request = $app['request_factory']();
$router = $app['router'];

$router->dispatch($request)->send();
