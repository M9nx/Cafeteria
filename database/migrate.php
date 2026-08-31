<?php

declare(strict_types=1);

use Cafeteria\Core\Config\Environment;
use Cafeteria\Core\Database\ConnectionFactory;
use Cafeteria\Core\Database\Migrator;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Composer dependencies are missing. Run composer install.\n");
    exit(1);
}

require $autoload;

try {
    Environment::load($root . '/.env');

    $config = require $root . '/config/database.php';
    $connection = (new ConnectionFactory())->make($config);
    $migrator = new Migrator($connection, $root . '/database/migrations');
    $applied = $migrator->up();

    if ($applied === []) {
        fwrite(STDOUT, "Database is already up to date.\n");
        exit(0);
    }

    foreach ($applied as $migration) {
        fwrite(STDOUT, "Applied: {$migration}\n");
    }

    fwrite(STDOUT, "Migration complete.\n");
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, "Migration failed: {$exception->getMessage()}\n");
    exit(1);
}
