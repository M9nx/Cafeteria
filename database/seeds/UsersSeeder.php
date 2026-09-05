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

    public const MOUNIR_EMAIL = 'revolutionary516@uberip.com';
    public const SALMA_EMAIL = 'salmafathy274@gmail.com';
    public const HANA_EMAIL = 'hanakotb14@gmail.com';
    public const BASHA_EMAIL = 'bashawahed573@gmail.com';

    /**
     * @var list<array{
     *     name: string,
     *     email: string,
     *     role: string,
     *     room: string,
     *     extension: string
     * }>
     */
    private const USERS = [
        [
            'name' => 'Demo Admin',
            'email' => self::DEV_ADMIN_EMAIL,
            'role' => 'ADMIN',
            'room' => 'Room 101',
            'extension' => '1001',
        ],
        [
            'name' => 'Demo User',
            'email' => self::DEV_USER_EMAIL,
            'role' => 'USER',
            'room' => 'Room 101',
            'extension' => '1002',
        ],
        [
            'name' => 'Mounir',
            'email' => self::MOUNIR_EMAIL,
            'role' => 'ADMIN',
            'room' => 'Room 101',
            'extension' => '2001',
        ],
        [
            'name' => 'Salma Fathy',
            'email' => self::SALMA_EMAIL,
            'role' => 'ADMIN',
            'room' => 'Room 102',
            'extension' => '2002',
        ],
        [
            'name' => 'Hana',
            'email' => self::HANA_EMAIL,
            'role' => 'USER',
            'room' => 'Reception',
            'extension' => '3001',
        ],
        [
            'name' => 'Basha Gebril',
            'email' => self::BASHA_EMAIL,
            'role' => 'USER',
            'room' => 'Conference Hall',
            'extension' => '3002',
        ],
    ];

    public function __construct(private readonly PDO $connection) {}

    public function name(): string
    {
        return 'users';
    }

    public function run(): void
    {
        $hash = password_hash(self::DEV_PASSWORD, PASSWORD_DEFAULT);

        foreach (self::USERS as $user) {
            $this->upsertUser([
                'name' => $user['name'],
                'email' => strtolower(trim($user['email'])),
                'password_hash' => $hash,
                'role' => $user['role'],
                'room_id' => $this->roomId($user['room']),
                'extension' => $user['extension'],
            ]);
        }
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
