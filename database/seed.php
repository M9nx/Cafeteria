<?php

declare(strict_types=1);

use Cafeteria\Core\Config\Environment;
use Cafeteria\Core\Database\ConnectionFactory;
use Cafeteria\Database\Seeds\SeedRunner;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
require $root . '/database/seeds/SeedRunner.php';
require $root . '/database/seeds/RoomsSeeder.php';
require $root . '/database/seeds/CategoriesSeeder.php';
require $root . '/database/seeds/ProductsSeeder.php';
require $root . '/database/seeds/UsersSeeder.php';
require $root . '/database/seeds/OrdersSeeder.php';

try {
    Environment::load($root . '/.env');
    $config = require $root . '/config/database.php';
    $pdo = (new ConnectionFactory())->make($config);

    $runner = new SeedRunner($pdo);
    $applied = $runner->run();

    foreach ($applied as $seeder) {
        fwrite(STDOUT, "Seeded: {$seeder}\n");
    }

    fwrite(STDOUT, "Seed complete.\n");
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "Seed failed: {$e->getMessage()}\n");
    exit(1);
}
