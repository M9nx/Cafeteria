<?php

declare(strict_types=1);

namespace Cafeteria\Database\Seeds;

use PDO;
use RuntimeException;

final class UsersSeeder
{
    public const DEV_ADMIN_EMAIL = 'admin@example.test';
    public const DEV_USER_EMAIL = 'user@example.test';
    public const DEV_PASSWORD = 'DevPassword123!';

    public function __construct(private readonly PDO $connection) {}

    public function name(): string
    {
        return 'users';
    }

    public function run(): void
    {
        $roomId = $this->roomId('Room 101');
        $hash = password_hash(self::DEV_PASSWORD, PASSWORD_DEFAULT);

        $this->upsertUser([
            'name' => 'Demo Admin',
            'email' => self::DEV_ADMIN_EMAIL,
            'password_hash' => $hash,
            'role' => 'ADMIN',
            'room_id' => $roomId,
            'extension' => '1001',
        ]);

        $this->upsertUser([
            'name' => 'Demo User',
            'email' => self::DEV_USER_EMAIL,
            'password_hash' => $hash,
            'role' => 'USER',
            'room_id' => $roomId,
            'extension' => '1002',
        ]);
    }

    /** @param array{name:string,email:string,password_hash:string,role:string,room_id:int,extension:string} $user */
    private function upsertUser(array $user): void
    {
        $sql = <<<'SQL'
            INSERT INTO users
                (name, email, password_hash, role, room_id, extension, is_active)
            VALUES
                (:name, :email, :password_hash, :role, :room_id, :extension, 1)
            ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                password_hash = VALUES(password_hash),
                role = VALUES(role),
                room_id = VALUES(room_id),
                extension = VALUES(extension),
                is_active = 1,
                updated_at = CURRENT_TIMESTAMP
            SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($user);
    }

    private function roomId(string $name): int
    {
        $stmt = $this->connection->prepare(
            'SELECT id FROM rooms WHERE name = :name LIMIT 1'
        );
        $stmt->execute(['name' => $name]);
        $id = $stmt->fetchColumn();

        if ($id === false) {
            throw new RuntimeException("Missing room for user seed: {$name}");
        }

        return (int) $id;
    }
}
