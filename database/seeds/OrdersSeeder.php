<?php

declare(strict_types=1);

namespace Cafeteria\Database\Seeds;

use DateInterval;
use DateTimeImmutable;
use PDO;
use RuntimeException;

/**
 * Seeds realistic demo orders for every known seed user (idempotent via notes marker).
 */
final class OrdersSeeder
{
    private const NOTE_MARKER = '[demo-seed]';

    public function __construct(private readonly PDO $connection) {}

    public function name(): string
    {
        return 'orders';
    }

    public function run(): void
    {
        $users = $this->seedUsers();
        $products = $this->availableProducts();
        $rooms = $this->activeRooms();
        $adminId = $this->userIdByEmail(UsersSeeder::MOUNIR_EMAIL)
            ?? $this->userIdByEmail(UsersSeeder::DEV_ADMIN_EMAIL);

        if ($users === [] || $products === [] || $rooms === [] || $adminId === null) {
            throw new RuntimeException('Orders seeder requires users, products, and rooms.');
        }

        $productIndex = 0;
        $roomIndex = 0;
        $baseTime = new DateTimeImmutable('now');

        foreach (array_values($users) as $userOffset => $user) {
            $userId = (int) $user['id'];

            if ($this->hasSeedOrders($userId)) {
                continue;
            }

            $scenarios = $this->scenariosForUser($userOffset);

            foreach ($scenarios as $scenarioOffset => $scenario) {
                $createdAt = $baseTime
                    ->sub(new DateInterval('P' . ($userOffset + $scenarioOffset + 1) . 'D'))
                    ->sub(new DateInterval('PT' . (($scenarioOffset + 1) * 3) . 'H'));

                $lineSpecs = $scenario['lines'];
                $lines = [];
                $totalCents = 0;

                foreach ($lineSpecs as $qty) {
                    $product = $products[$productIndex % count($products)];
                    $productIndex++;

                    $unitCents = $this->toCents((string) $product['price']);
                    $lineCents = $unitCents * $qty;
                    $totalCents += $lineCents;

                    $lines[] = [
                        'product_id' => (int) $product['id'],
                        'product_name_snapshot' => (string) $product['name'],
                        'unit_price_snapshot' => $this->fromCents($unitCents),
                        'quantity' => $qty,
                        'line_total' => $this->fromCents($lineCents),
                    ];
                }

                $room = $rooms[$roomIndex % count($rooms)];
                $roomIndex++;

                $createdBy = $scenario['on_behalf']
                    ? $adminId
                    : $userId;

                $orderId = $this->insertOrder(
                    userId: $userId,
                    createdByUserId: $createdBy,
                    roomId: (int) $room['id'],
                    status: 'PROCESSING',
                    notes: self::NOTE_MARKER . ' ' . $scenario['notes'],
                    totalAmount: $this->fromCents($totalCents),
                    createdAt: $createdAt,
                    cancelledAt: null,
                );

                $this->insertItems($orderId, $lines);
                $this->insertHistory(
                    $orderId,
                    null,
                    'PROCESSING',
                    $createdBy,
                    $createdAt,
                );

                $this->applyLifecycle(
                    $orderId,
                    $scenario['status'],
                    $adminId,
                    $createdAt,
                );
            }
        }
    }

    /**
     * @return list<array{status:string,notes:string,on_behalf:bool,lines:list<int>}>
     */
    private function scenariosForUser(int $offset): array
    {
        $rotations = [
            [
                ['status' => 'PROCESSING', 'notes' => 'Morning tray', 'on_behalf' => false, 'lines' => [1, 2]],
                ['status' => 'OUT_FOR_DELIVERY', 'notes' => 'Desk delivery', 'on_behalf' => false, 'lines' => [1]],
                ['status' => 'DONE', 'notes' => 'Completed lunch', 'on_behalf' => true, 'lines' => [2, 1]],
            ],
            [
                ['status' => 'PROCESSING', 'notes' => 'Hot drinks', 'on_behalf' => false, 'lines' => [2]],
                ['status' => 'CANCELLED', 'notes' => 'Changed mind', 'on_behalf' => false, 'lines' => [1]],
                ['status' => 'DONE', 'notes' => 'Team snack', 'on_behalf' => false, 'lines' => [1, 1, 1]],
            ],
            [
                ['status' => 'OUT_FOR_DELIVERY', 'notes' => 'Conference refreshments', 'on_behalf' => true, 'lines' => [2, 2]],
                ['status' => 'PROCESSING', 'notes' => 'Quick coffee', 'on_behalf' => false, 'lines' => [1]],
                ['status' => 'DONE', 'notes' => 'Bakery box', 'on_behalf' => false, 'lines' => [1, 2]],
            ],
        ];

        return $rotations[$offset % count($rotations)];
    }

    private function applyLifecycle(
        int $orderId,
        string $targetStatus,
        int $adminId,
        DateTimeImmutable $createdAt,
    ): void {
        if ($targetStatus === 'PROCESSING') {
            return;
        }

        if ($targetStatus === 'CANCELLED') {
            $at = $createdAt->add(new DateInterval('PT20M'));
            $this->updateOrderStatus($orderId, 'CANCELLED', $at, $at);
            $this->insertHistory($orderId, 'PROCESSING', 'CANCELLED', $adminId, $at);

            return;
        }

        $outAt = $createdAt->add(new DateInterval('PT15M'));
        $this->updateOrderStatus($orderId, 'OUT_FOR_DELIVERY', $outAt, null);
        $this->insertHistory($orderId, 'PROCESSING', 'OUT_FOR_DELIVERY', $adminId, $outAt);

        if ($targetStatus === 'OUT_FOR_DELIVERY') {
            return;
        }

        if ($targetStatus === 'DONE') {
            $doneAt = $outAt->add(new DateInterval('PT25M'));
            $this->updateOrderStatus($orderId, 'DONE', $doneAt, null);
            $this->insertHistory($orderId, 'OUT_FOR_DELIVERY', 'DONE', $adminId, $doneAt);
        }
    }

    private function updateOrderStatus(
        int $orderId,
        string $status,
        DateTimeImmutable $updatedAt,
        ?DateTimeImmutable $cancelledAt,
    ): void {
        $stmt = $this->connection->prepare(
            'UPDATE orders
             SET status = :status,
                 cancelled_at = :cancelled_at,
                 updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([
            'status' => $status,
            'cancelled_at' => $cancelledAt?->format('Y-m-d H:i:s'),
            'updated_at' => $updatedAt->format('Y-m-d H:i:s'),
            'id' => $orderId,
        ]);
    }

    /**
     * @param list<array{
     *     product_id:int,
     *     product_name_snapshot:string,
     *     unit_price_snapshot:string,
     *     quantity:int,
     *     line_total:string
     * }> $lines
     */
    private function insertItems(int $orderId, array $lines): void
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO order_items
                (order_id, product_id, product_name_snapshot,
                 unit_price_snapshot, quantity, line_total)
             VALUES
                (:order_id, :product_id, :product_name_snapshot,
                 :unit_price_snapshot, :quantity, :line_total)'
        );

        foreach ($lines as $line) {
            $stmt->execute([
                'order_id' => $orderId,
                'product_id' => $line['product_id'],
                'product_name_snapshot' => $line['product_name_snapshot'],
                'unit_price_snapshot' => $line['unit_price_snapshot'],
                'quantity' => $line['quantity'],
                'line_total' => $line['line_total'],
            ]);
        }
    }

    private function insertOrder(
        int $userId,
        int $createdByUserId,
        int $roomId,
        string $status,
        string $notes,
        string $totalAmount,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $cancelledAt,
    ): int {
        $stmt = $this->connection->prepare(
            'INSERT INTO orders
                (user_id, created_by_user_id, room_id, status, notes,
                 total_amount, created_at, updated_at, cancelled_at)
             VALUES
                (:user_id, :created_by_user_id, :room_id, :status, :notes,
                 :total_amount, :created_at, :updated_at, :cancelled_at)'
        );

        $stamp = $createdAt->format('Y-m-d H:i:s');
        $stmt->execute([
            'user_id' => $userId,
            'created_by_user_id' => $createdByUserId,
            'room_id' => $roomId,
            'status' => $status,
            'notes' => $notes,
            'total_amount' => $totalAmount,
            'created_at' => $stamp,
            'updated_at' => $stamp,
            'cancelled_at' => $cancelledAt?->format('Y-m-d H:i:s'),
        ]);

        $orderId = (int) $this->connection->lastInsertId();

        if ($orderId < 1) {
            throw new RuntimeException('Unable to seed order.');
        }

        return $orderId;
    }

    private function insertHistory(
        int $orderId,
        ?string $fromStatus,
        string $toStatus,
        int $changedByUserId,
        DateTimeImmutable $changedAt,
    ): void {
        $stmt = $this->connection->prepare(
            'INSERT INTO order_status_history
                (order_id, from_status, to_status, changed_by_user_id, changed_at)
             VALUES
                (:order_id, :from_status, :to_status, :changed_by_user_id, :changed_at)'
        );
        $stmt->execute([
            'order_id' => $orderId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by_user_id' => $changedByUserId,
            'changed_at' => $changedAt->format('Y-m-d H:i:s'),
        ]);
    }

    private function hasSeedOrders(int $userId): bool
    {
        $stmt = $this->connection->prepare(
            'SELECT id FROM orders
             WHERE user_id = :user_id
               AND notes LIKE :marker
             LIMIT 1'
        );
        $stmt->execute([
            'user_id' => $userId,
            'marker' => self::NOTE_MARKER . '%',
        ]);

        return $stmt->fetchColumn() !== false;
    }

    /** @return list<array{id:int|string,name:string,email:string,role:string}> */
    private function seedUsers(): array
    {
        $emails = [
            UsersSeeder::DEV_ADMIN_EMAIL,
            UsersSeeder::DEV_USER_EMAIL,
            UsersSeeder::MOUNIR_EMAIL,
            UsersSeeder::SALMA_EMAIL,
            UsersSeeder::HANA_EMAIL,
            UsersSeeder::BASHA_EMAIL,
        ];

        $placeholders = implode(',', array_fill(0, count($emails), '?'));
        $stmt = $this->connection->prepare(
            "SELECT id, name, email, role
             FROM users
             WHERE email IN ({$placeholders})
               AND is_active = 1
             ORDER BY id ASC"
        );
        $stmt->execute($emails);

        /** @var list<array{id:int|string,name:string,email:string,role:string}> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }

    /** @return list<array{id:int|string,name:string,price:string}> */
    private function availableProducts(): array
    {
        $stmt = $this->connection->query(
            'SELECT id, name, price
             FROM products
             WHERE is_available = 1
               AND deleted_at IS NULL
             ORDER BY id ASC'
        );

        /** @var list<array{id:int|string,name:string,price:string}> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }

    /** @return list<array{id:int|string,name:string}> */
    private function activeRooms(): array
    {
        $stmt = $this->connection->query(
            'SELECT id, name
             FROM rooms
             WHERE is_active = 1
             ORDER BY id ASC'
        );

        /** @var list<array{id:int|string,name:string}> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }

    private function userIdByEmail(string $email): ?int
    {
        $stmt = $this->connection->prepare(
            'SELECT id FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => strtolower(trim($email))]);
        $id = $stmt->fetchColumn();

        return $id === false ? null : (int) $id;
    }

    private function toCents(string $amount): int
    {
        if (preg_match('/^\d+(\.\d{1,2})?$/', $amount) !== 1) {
            throw new RuntimeException("Invalid money amount: {$amount}");
        }

        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');
        $fraction = str_pad(substr($fraction, 0, 2), 2, '0');

        return ((int) $whole * 100) + (int) $fraction;
    }

    private function fromCents(int $cents): string
    {
        return sprintf('%d.%02d', intdiv($cents, 100), $cents % 100);
    }
}
