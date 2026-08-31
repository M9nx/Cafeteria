<?php

declare(strict_types=1);

namespace Cafeteria\Database\Seeds;

use PDO;

final class RoomsSeeder
{
    /** @var list<string> */
    private const ROOMS = ['Room 101', 'Room 102', 'Reception'];

    public function __construct(private readonly PDO $connection) {}

    public function name(): string
    {
        return 'rooms';
    }

    public function run(): void
    {
        $sql = <<<'SQL'
            INSERT INTO rooms (name, is_active)
            VALUES (:name, 1)
            ON DUPLICATE KEY UPDATE
                is_active = VALUES(is_active),
                updated_at = CURRENT_TIMESTAMP
            SQL;

        $stmt = $this->connection->prepare($sql);

        foreach (self::ROOMS as $room) {
            $stmt->execute(['name' => $room]);
        }
    }
}
