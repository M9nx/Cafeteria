<?php

declare(strict_types=1);

namespace Cafeteria\Core\Database;

use InvalidArgumentException;
use PDO;

final class ConnectionFactory
{
    /**
     * @param array{
     *   driver:string,
     *   host:string,
     *   port:int,
     *   database:string,
     *   username:string,
     *   password:?string,
     *   charset:string
     * } $config
     */
    public function make(array $config): PDO
    {
        if (($config['driver'] ?? null) !== 'mysql') {
            throw new InvalidArgumentException('Only the mysql driver is supported.');
        }

        $charset = $config['charset'] ?? 'utf8mb4';

        if (!in_array($charset, ['utf8mb4'], true)) {
            throw new InvalidArgumentException('Unsupported database charset.');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $charset
        );

        $connection = new PDO(
            $dsn,
            $config['username'],
            $config['password'] ?? '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ]
        );

        $connection->exec("SET time_zone = '+00:00'");

        return $connection;
    }

}