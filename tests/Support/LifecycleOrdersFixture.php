<?php

declare(strict_types=1);

namespace Tests\Support;

use InvalidArgumentException;
use PDO;
use RuntimeException;

final class LifecycleOrdersFixture
{
    /** @var array<string, array<string, mixed>>|null */
    private static ?array $orders = null;

    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        if (self::$orders === null) {
            /** @var array<string, mixed> $fixture */
            $fixture = require dirname(__DIR__) . '/Fixtures/orders.php';

            if (!isset($fixture['lifecycle_orders']) || !is_array($fixture['lifecycle_orders'])) {
                throw new RuntimeException('Missing lifecycle_orders in tests/Fixtures/orders.php');
            }

            self::$orders = $fixture['lifecycle_orders'];
        }

        return self::$orders;
    }

    /** @return array<string, mixed> */
    public static function get(string $key): array
    {
        $orders = self::all();

        if (!isset($orders[$key])) {
            throw new InvalidArgumentException("Unknown lifecycle order fixture: {$key}");
        }

        return $orders[$key];
    }

    public static function id(string $key): int
    {
        return (int) self::get($key)['id'];
    }

    /**
     * @param list<string> $keys
     *
     * @return array<int, array{id: int, user_id: int, status: string}>
     */
    public static function cancellationFakeOrders(array $keys): array
    {
        $orders = [];

        foreach ($keys as $key) {
            $row = self::get($key);
            $id = (int) $row['id'];

            $orders[$id] = [
                'id' => $id,
                'user_id' => (int) $row['user_id'],
                'status' => (string) $row['status'],
            ];
        }

        return $orders;
    }

    public static function seedSqliteOrder(PDO $pdo, string $key): void
    {
        $row = self::get($key);

        $statement = $pdo->prepare(
            'INSERT INTO orders
                (id, user_id, created_by_user_id, room_id, status, notes, total_amount, created_at, updated_at, cancelled_at)
             VALUES
                (:id, :user_id, :created_by_user_id, :room_id, :status, NULL, :total_amount, :created_at, :created_at, :cancelled_at)'
        );

        $statement->execute([
            'id' => (int) $row['id'],
            'user_id' => (int) $row['user_id'],
            'created_by_user_id' => (int) $row['created_by_user_id'],
            'room_id' => (int) $row['room_id'],
            'status' => (string) $row['status'],
            'total_amount' => (string) $row['total_amount'],
            'created_at' => (string) $row['created_at'],
            'cancelled_at' => $row['cancelled_at'] ?? null,
        ]);
    }

    /**
     * @param list<string> $keys
     *
     * @return list<int>
     */
    public static function seedMysql(PDO $pdo, array $keys): array
    {
        self::ensureMysqlUser($pdo, 3, 'other.user@example.test', 'Other User');

        $ids = [];

        foreach ($keys as $key) {
            $row = self::get($key);
            $id = (int) $row['id'];
            $ids[] = $id;

            $statement = $pdo->prepare(
                'INSERT INTO orders
                    (id, user_id, created_by_user_id, room_id, status, notes, total_amount, created_at, updated_at, cancelled_at)
                 VALUES
                    (:id, :user_id, :created_by_user_id, :room_id, :status, NULL, :total_amount, :created_at, :updated_at, :cancelled_at)
                 ON DUPLICATE KEY UPDATE
                    user_id = VALUES(user_id),
                    created_by_user_id = VALUES(created_by_user_id),
                    room_id = VALUES(room_id),
                    status = VALUES(status),
                    total_amount = VALUES(total_amount),
                    created_at = VALUES(created_at),
                    updated_at = VALUES(updated_at),
                    cancelled_at = VALUES(cancelled_at)'
            );

            $createdAt = (string) $row['created_at'];

            $statement->execute([
                'id' => $id,
                'user_id' => (int) $row['user_id'],
                'created_by_user_id' => (int) $row['created_by_user_id'],
                'room_id' => self::resolveRoomId($pdo, (int) $row['room_id']),
                'status' => (string) $row['status'],
                'total_amount' => (string) $row['total_amount'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'cancelled_at' => $row['cancelled_at'] ?? null,
            ]);
        }

        return $ids;
    }

    /**
     * @param list<int> $ids
     */
    public static function deleteMysqlOrders(PDO $pdo, array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $deleteItems = $pdo->prepare(
            "DELETE FROM order_items WHERE order_id IN ({$placeholders})"
        );
        $deleteItems->execute($ids);

        $deleteOrders = $pdo->prepare(
            "DELETE FROM orders WHERE id IN ({$placeholders})"
        );
        $deleteOrders->execute($ids);
    }

    public static function ensureMysqlUser(
        PDO $pdo,
        int $id,
        string $email,
        string $name,
        string $role = 'USER',
    ): void {
        $roomId = self::resolveRoomId($pdo, 1);

        $statement = $pdo->prepare(
            'INSERT INTO users
                (id, name, email, password_hash, role, room_id, extension, is_active)
             VALUES
                (:id, :name, :email, :password_hash, :role, :room_id, :extension, 1)
             ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                email = VALUES(email),
                role = VALUES(role),
                room_id = VALUES(room_id),
                is_active = 1'
        );

        $statement->execute([
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash('DevPassword123!', PASSWORD_DEFAULT),
            'role' => $role,
            'room_id' => $roomId,
            'extension' => '100' . $id,
        ]);
    }

    private static function resolveRoomId(PDO $pdo, int $preferredId): int
    {
        $preferred = $pdo->prepare(
            'SELECT id FROM rooms WHERE id = :id LIMIT 1'
        );
        $preferred->execute(['id' => $preferredId]);
        $roomId = $preferred->fetchColumn();

        if ($roomId !== false) {
            return (int) $roomId;
        }

        $fallback = $pdo->query(
            'SELECT id FROM rooms WHERE is_active = 1 ORDER BY id ASC LIMIT 1'
        );

        $roomId = $fallback !== false ? $fallback->fetchColumn() : false;

        if ($roomId === false) {
            throw new RuntimeException('No active room available for lifecycle fixture seeding.');
        }

        return (int) $roomId;
    }
}
