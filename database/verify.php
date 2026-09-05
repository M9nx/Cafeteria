<?php

declare(strict_types=1);

use Cafeteria\Core\Config\Environment;
use Cafeteria\Core\Database\ConnectionFactory;
use Cafeteria\Database\Seeds\UsersSeeder;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
require $root . '/database/seeds/UsersSeeder.php';

$failures = [];

try {
    Environment::load($root . '/.env');
    $pdo = (new ConnectionFactory())->make(require $root . '/config/database.php');

    $expect = [
        'rooms' => 4,
        'categories' => 4,
        'products' => 12,
        'users' => 6,
        'orders' => 12,
        'order_items' => 12,
    ];

    foreach ($expect as $table => $count) {
        $actual = (int) $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
        if ($actual < $count) {
            $failures[] = "{$table}: expected >= {$count}, got {$actual}";
        }
    }

    $orphans = (int) $pdo->query(
        'SELECT COUNT(*)
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE c.id IS NULL'
    )->fetchColumn();
    if ($orphans > 0) {
        $failures[] = "products with invalid category_id: {$orphans}";
    }

    $badPrices = (int) $pdo->query(
        'SELECT COUNT(*) FROM products WHERE price <= 0'
    )->fetchColumn();
    if ($badPrices > 0) {
        $failures[] = "products with non-positive price: {$badPrices}";
    }

    $stmt = $pdo->prepare(
        'SELECT password_hash FROM users WHERE email = :email LIMIT 1'
    );
    $stmt->execute(['email' => UsersSeeder::DEV_ADMIN_EMAIL]);
    $hash = $stmt->fetchColumn();

    if (
        $hash === false
        || !password_verify(UsersSeeder::DEV_PASSWORD, (string) $hash)
    ) {
        $failures[] = 'admin password hash verification failed';
    }

    if ($failures !== []) {
        foreach ($failures as $failure) {
            fwrite(STDERR, "VERIFY FAIL: {$failure}\n");
        }
        exit(1);
    }

    fwrite(STDOUT, "Verify OK.\n");
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "Verify failed: {$e->getMessage()}\n");
    exit(1);
}
