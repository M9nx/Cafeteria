<?php

declare(strict_types=1);

use Cafeteria\Core\Config\Environment;
use Cafeteria\Core\Database\ConnectionFactory;
use PDO;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

Environment::load($root . '/.env');

$app = require $root . '/config/app.php';
$dbName = Environment::required('DB_NAME');
$env = strtolower((string) ($app['environment'] ?? 'production'));

if ($env === 'production') {
    fwrite(STDERR, "Refusing rebuild when APP_ENV=production.\n");
    exit(1);
}

if (
    !str_ends_with($dbName, '_dev')
    && !str_ends_with($dbName, '_test')
) {
    fwrite(STDERR, "Refusing rebuild: DB_NAME must end with _dev or _test.\n");
    exit(1);
}

try {
    $pdo = (new ConnectionFactory())->make(require $root . '/config/database.php');

    fwrite(STDOUT, "Dropping all tables...\n");

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

    $tables = $pdo
        ->query('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"')
        ->fetchAll(PDO::FETCH_NUM);

    foreach ($tables as [$table]) {
        $escaped = str_replace('`', '``', (string) $table);
        $pdo->exec("DROP TABLE IF EXISTS `{$escaped}`");
        fwrite(STDOUT, "Dropped: {$table}\n");
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

    $commands = [
        ['migrate', PHP_BINARY, $root . '/database/migrate.php'],
        ['seed', PHP_BINARY, $root . '/database/seed.php'],
        ['verify', PHP_BINARY, $root . '/database/verify.php'],
    ];

    foreach ($commands as [$label, $binary, $script]) {
        fwrite(STDOUT, "Running {$label}...\n");

        $command = escapeshellarg($binary) . ' ' . escapeshellarg($script);
        passthru($command, $exitCode);

        if ($exitCode !== 0) {
            fwrite(STDERR, "Rebuild failed during {$label} (exit {$exitCode}).\n");
            exit(1);
        }
    }

    fwrite(STDOUT, "Rebuild complete.\n");
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "Rebuild failed: {$e->getMessage()}\n");
    exit(1);
}
