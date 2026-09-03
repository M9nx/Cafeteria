<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use Cafeteria\Repositories\Pdo\PdoOrderQueryRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\Support\LifecycleOrdersFixture;

final class OrderQueueTest extends TestCase
{
    private PDO $pdo;
    private PdoOrderQueryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdo = $this->freshDatabase();
        $this->repository = new PdoOrderQueryRepository($this->pdo);
    }

    public function test_queue_reads_active_and_terminal_rows_from_lifecycle_fixture(): void
    {
        $this->pdo->exec(
            "INSERT INTO users (id, name, email, role) VALUES (2, 'Demo Owner', 'owner@example.test', 'USER')"
        );

        LifecycleOrdersFixture::seedSqliteOrder($this->pdo, 'processing_order');
        LifecycleOrdersFixture::seedSqliteOrder($this->pdo, 'done_order');

        $queue = $this->repository->listCurrentQueue(1, 15);
        $ids = array_map(static fn (array $row): int => (int) $row['id'], $queue['items']);

        self::assertSame(
            [LifecycleOrdersFixture::id('processing_order')],
            $ids,
        );
        self::assertSame(1, $queue['total']);
    }

    public function test_queue_includes_only_active_statuses(): void
    {
        $this->seedOrder(1, 'PROCESSING', '2026-09-01 09:00:00');
        $this->seedOrder(2, 'OUT_FOR_DELIVERY', '2026-09-01 09:05:00');
        $this->seedOrder(3, 'DONE', '2026-09-01 09:10:00');
        $this->seedOrder(4, 'CANCELLED', '2026-09-01 09:15:00');

        $queue = $this->repository->listCurrentQueue(1, 15);

        $ids = array_map(static fn (array $row): int => (int) $row['id'], $queue['items']);

        self::assertSame([1, 2], $ids, 'Only PROCESSING and OUT_FOR_DELIVERY orders belong in the active queue.');
        self::assertSame(2, $queue['total'], 'DONE/CANCELLED orders must not inflate the queue total.');
    }

    public function test_queue_excludes_done_and_cancelled_even_when_they_are_most_recent(): void
    {
        // A DONE order created after everything else must still not appear,
        // proving the filter is on status, not just recency.
        $this->seedOrder(1, 'PROCESSING', '2026-09-01 09:00:00');
        $this->seedOrder(2, 'DONE', '2026-09-01 23:59:59');
        $this->seedOrder(3, 'CANCELLED', '2026-09-02 00:00:00');

        $queue = $this->repository->listCurrentQueue(1, 15);

        self::assertSame([1], array_map(
            static fn (array $row): int => (int) $row['id'],
            $queue['items']
        ));
        self::assertSame(1, $queue['total']);
    }

    public function test_queue_orders_oldest_active_order_first(): void
    {
   
        $this->seedOrder(1, 'OUT_FOR_DELIVERY', '2026-09-01 12:00:00');
        $this->seedOrder(2, 'PROCESSING', '2026-09-01 08:00:00');
        $this->seedOrder(3, 'PROCESSING', '2026-09-01 10:00:00');

        $queue = $this->repository->listCurrentQueue(1, 15);

        self::assertSame(
            [2, 3, 1],
            array_map(static fn (array $row): int => (int) $row['id'], $queue['items']),
            'Queue must be ordered oldest-active-first (created_at ASC, id ASC).'
        );
    }

    public function test_queue_pagination_excludes_terminal_orders_from_the_page_math(): void
    {
        $this->seedOrder(1, 'PROCESSING', '2026-09-01 08:00:00');
        $this->seedOrder(2, 'DONE', '2026-09-01 08:30:00');
        $this->seedOrder(3, 'PROCESSING', '2026-09-01 09:00:00');
        $this->seedOrder(4, 'OUT_FOR_DELIVERY', '2026-09-01 10:00:00');
        $this->seedOrder(5, 'CANCELLED', '2026-09-01 11:00:00');

        $pageOne = $this->repository->listCurrentQueue(1, 2);
        self::assertSame(3, $pageOne['total']);
        self::assertSame(
            [1, 3],
            array_map(static fn (array $row): int => (int) $row['id'], $pageOne['items'])
        );

        $pageTwo = $this->repository->listCurrentQueue(2, 2);
        self::assertSame(3, $pageTwo['total']);
        self::assertSame(
            [4],
            array_map(static fn (array $row): int => (int) $row['id'], $pageTwo['items'])
        );
    }

    public function test_queue_returns_empty_when_no_active_orders_exist(): void
    {
        $this->seedOrder(1, 'DONE', '2026-09-01 08:00:00');
        $this->seedOrder(2, 'CANCELLED', '2026-09-01 09:00:00');

        $queue = $this->repository->listCurrentQueue(1, 15);

        self::assertSame([], $queue['items']);
        self::assertSame(0, $queue['total']);
    }

    private function freshDatabase(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec('
            CREATE TABLE rooms (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL
            )
        ');

        $pdo->exec('
            CREATE TABLE users (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT "USER"
            )
        ');

        $pdo->exec('
            CREATE TABLE orders (
                id INTEGER PRIMARY KEY,
                user_id INTEGER NOT NULL,
                created_by_user_id INTEGER NOT NULL,
                room_id INTEGER NOT NULL,
                status TEXT NOT NULL DEFAULT "PROCESSING",
                notes TEXT NULL,
                total_amount TEXT NOT NULL,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL,
                cancelled_at TEXT NULL
            )
        ');

        $pdo->exec("INSERT INTO rooms (id, name) VALUES (1, 'Room A')");
        $pdo->exec("INSERT INTO users (id, name, email, role) VALUES (1, 'Demo User', 'user@example.test', 'USER')");

        return $pdo;
    }

    private function seedOrder(int $id, string $status, string $createdAt): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO orders
                (id, user_id, created_by_user_id, room_id, status, notes, total_amount, created_at, updated_at, cancelled_at)
             VALUES
                (:id, 1, 1, 1, :status, NULL, "10.00", :created_at, :created_at, :cancelled_at)'
        );

        $statement->execute([
            'id' => $id,
            'status' => $status,
            'created_at' => $createdAt,
            'cancelled_at' => $status === 'CANCELLED' ? $createdAt : null,
        ]);
    }
}