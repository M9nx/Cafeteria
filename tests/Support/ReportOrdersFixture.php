<?php

declare(strict_types=1);

namespace Tests\Support;

use PDO;

final class ReportOrdersFixture
{
    public int $userAId;
    public int $userBId;
    public int $roomId;

    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function generate(): self
    {
        // 1. Clean fixture tables
        $this->pdo->exec('DELETE FROM order_items');
        $this->pdo->exec('DELETE FROM orders');
        $this->pdo->exec('DELETE FROM users WHERE email LIKE "%@fixture.com"');
        $this->pdo->exec('DELETE FROM rooms WHERE name = "Fixture Room"');

        // 2. Insert Room dependency
        $stmtRoom = $this->pdo->prepare('INSERT INTO rooms (name) VALUES (:name)');
        $stmtRoom->execute(['name' => 'Fixture Room']);
        $this->roomId = (int) $this->pdo->lastInsertId();

        // 3. Insert Users
        $stmtUser = $this->pdo->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
        
        $stmtUser->execute([
            'name' => 'User A (Report Target)',
            'email' => 'user_a@fixture.com',
            'password' => password_hash('password', PASSWORD_BCRYPT),
        ]);
        $this->userAId = (int) $this->pdo->lastInsertId();

        $stmtUser->execute([
            'name' => 'User B (Control Group)',
            'email' => 'user_b@fixture.com',
            'password' => password_hash('password', PASSWORD_BCRYPT),
        ]);
        $this->userBId = (int) $this->pdo->lastInsertId();

        // 4. Insert Orders for User A
        // Order inside date range (2026-03-01 12:00:00) -> Amount: 100.00
        $this->createOrder($this->userAId, 100.00, 'DELIVERED', '2026-03-01 12:00:00');

        // Order exactly on 'FROM' date boundary (2026-03-01 00:00:00) -> Amount: 100.00
        $this->createOrder($this->userAId, 100.00, 'COMPLETED', '2026-03-01 00:00:00');

        // Order exactly on 'TO' date boundary (2026-03-01 23:59:59) -> Amount: 100.00
        $this->createOrder($this->userAId, 100.00, 'COMPLETED', '2026-03-01 23:59:59');

        // Cancelled Order inside range -> Amount: 500.00 (Excluded by default)
        $this->createOrder($this->userAId, 500.00, 'CANCELLED', '2026-03-01 15:00:00');

        // Order strictly BEFORE boundary (2026-02-28 23:59:59) -> Amount: 50.00
        $this->createOrder($this->userAId, 50.00, 'COMPLETED', '2026-02-28 23:59:59');

        // Order strictly AFTER boundary (2026-03-02 00:00:00) -> Amount: 50.00
        $this->createOrder($this->userAId, 50.00, 'COMPLETED', '2026-03-02 00:00:00');

        // 5. Insert Order for User B (Control Group) -> Amount: 150.00
        $this->createOrder($this->userBId, 150.00, 'COMPLETED', '2026-03-01 12:00:00');

        return $this;
    }

    private function createOrder(int $userId, float $amount, string $status, string $createdAt): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO orders (user_id, room_id, status, total_amount, created_at, updated_at)
             VALUES (:user_id, :room_id, :status, :total_amount, :created_at, :updated_at)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'room_id' => $this->roomId,
            'status' => $status,
            'total_amount' => $amount,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}