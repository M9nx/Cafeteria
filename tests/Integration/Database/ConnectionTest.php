<?php

declare(strict_types=1);

namespace Tests\Integration\Database;

use Cafeteria\Core\Config\Environment;
use Cafeteria\Core\Database\ConnectionFactory;
use PDO;
use PHPUnit\Framework\TestCase;
use Throwable;

final class ConnectionTest extends TestCase
{
    public function testConnectsToExpectedMysqlTestDatabase(): void
    {
        $root = dirname(__DIR__, 3);

        Environment::load($root . '/.env');

        /** @var array{database: string} $config */
        $config = require $root . '/config/database.php';

        self::assertStringEndsWith(
            '_test',
            $config['database'],
            'Database integration tests must target a *_test database.'
        );

        try {
            $connection = (new ConnectionFactory())->make($config);
        } catch (Throwable $exception) {
            if (self::isCi()) {
                self::fail(
                    'CI must provide a reachable MySQL test database: '
                    . $exception->getMessage()
                );
            }

            self::markTestSkipped(
                'MySQL test database is not reachable locally: '
                . $exception->getMessage()
            );
        }

        self::assertSame(
            $config['database'],
            $connection->query('SELECT DATABASE()')->fetchColumn()
        );

        self::assertSame(
            'utf8mb4',
            $connection->query('SELECT @@character_set_connection')->fetchColumn()
        );

        $statement = $connection->prepare('SELECT :value AS prepared_value');
        self::assertNotFalse($statement);

        $statement->execute(['value' => 'prepared']);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        self::assertSame('prepared', $row['prepared_value'] ?? null);
    }

    private static function isCi(): bool
    {
        $value = getenv('CI');

        return is_string($value)
            && in_array(strtolower($value), ['1', 'true', 'yes'], true);
    }
}
