<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use Cafeteria\Repositories\Pdo\PdoOrderQueryRepository;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\Support\LifecycleOrdersFixture;

final class OrderDateBoundaryTest extends TestCase
{
    private const USER_ID = 2;
    private const FILTER_DAY = '2026-09-15';

    private PDO $pdo;
    private PdoOrderQueryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdo = $this->freshDatabase();
        $this->repository = new PdoOrderQueryRepository($this->pdo);
    }

    public function test_order_at_exact_start_of_from_day_is_included(): void
    {
        LifecycleOrdersFixture::seedSqliteOrder($this->pdo, 'boundary_start_of_day');

        $result = $this->paginateForFilterDay();

        self::assertContainsOrderId(
            LifecycleOrdersFixture::id('boundary_start_of_day'),
            $result['items'],
        );
        self::assertSame(1, $result['total']);
    }

    public function test_order_at_exact_end_of_to_day_is_included(): void
    {
        LifecycleOrdersFixture::seedSqliteOrder($this->pdo, 'boundary_end_of_day');

        $result = $this->paginateForFilterDay();

        self::assertContainsOrderId(
            LifecycleOrdersFixture::id('boundary_end_of_day'),
            $result['items'],
        );
        self::assertSame(1, $result['total']);
    }

    public function test_order_one_second_before_from_day_is_excluded(): void
    {
        LifecycleOrdersFixture::seedSqliteOrder($this->pdo, 'boundary_one_second_before_range');

        $result = $this->paginateForFilterDay();

        self::assertNotContainsOrderId(
            LifecycleOrdersFixture::id('boundary_one_second_before_range'),
            $result['items'],
        );
        self::assertSame(0, $result['total']);
    }

    public function test_order_one_second_after_to_day_is_excluded(): void
    {
        LifecycleOrdersFixture::seedSqliteOrder($this->pdo, 'boundary_one_second_after_range');

        $result = $this->paginateForFilterDay();

        self::assertNotContainsOrderId(
            LifecycleOrdersFixture::id('boundary_one_second_after_range'),
            $result['items'],
        );
        self::assertSame(0, $result['total']);
    }

    public function test_multi_day_range_includes_both_boundary_days_fully(): void
    {
        $this->seedOrder(1, '2026-09-10 00:00:00'); // start boundary, start of range
        $this->seedOrder(2, '2026-09-12 12:00:00'); // middle
        $this->seedOrder(3, '2026-09-14 23:59:59'); // end boundary, end of range
        $this->seedOrder(4, '2026-09-09 23:59:59'); // just before range
        $this->seedOrder(5, '2026-09-15 00:00:00'); // just after range

        $result = $this->repository->paginateForUser(
            self::USER_ID,
            $this->startOfDay('2026-09-10'),
            $this->endOfDay('2026-09-14'),
            1,
            15,
        );

        $ids = array_map(static fn (array $row): int => (int) $row['id'], $result['items']);
        sort($ids);

        self::assertSame([1, 2, 3], $ids);
        self::assertSame(3, $result['total']);
    }

    public function test_open_ended_from_only_includes_everything_from_that_moment_onward(): void
    {
        $this->seedOrder(1, '2026-09-14 23:59:59'); // just before
        $this->seedOrder(2, '2026-09-15 00:00:00'); // exact boundary
        $this->seedOrder(3, '2026-09-20 00:00:00'); // well after

        $result = $this->repository->paginateForUser(
            self::USER_ID,
            $this->startOfDay(self::FILTER_DAY),
            null,
            1,
            15,
        );

        $ids = array_map(static fn (array $row): int => (int) $row['id'], $result['items']);
        sort($ids);

        self::assertSame([2, 3], $ids);
    }

    public function test_open_ended_to_only_includes_everything_up_to_that_moment(): void
    {
        $this->seedOrder(1, '2026-09-01 00:00:00'); // well before
        $this->seedOrder(2, '2026-09-15 23:59:59'); // exact boundary
        $this->seedOrder(3, '2026-09-16 00:00:00'); // just after

        $result = $this->repository->paginateForUser(
            self::USER_ID,
            null,
            $this->endOfDay(self::FILTER_DAY),
            1,
            15,
        );

        $ids = array_map(static fn (array $row): int => (int) $row['id'], $result['items']);
        sort($ids);

        self::assertSame([1, 2], $ids);
    }

    public function test_boundary_orders_belonging_to_another_user_are_never_included(): void
    {
        $this->seedOrder(1, '2026-09-15 12:00:00', userId: self::USER_ID);
        $this->seedOrder(2, '2026-09-15 12:00:00', userId: self::USER_ID + 1);

        $result = $this->paginateForFilterDay();

        self::assertContainsOrderId(1, $result['items']);
        self::assertNotContainsOrderId(2, $result['items']);
        self::assertSame(1, $result['total']);
    }

    /**
     * @return array{items: list<array<string, mixed>>, total: int, page: int, per_page: int}
     */
    private function paginateForFilterDay(): array
    {
        return $this->repository->paginateForUser(
            self::USER_ID,
            $this->startOfDay(self::FILTER_DAY),
            $this->endOfDay(self::FILTER_DAY),
            1,
            15,
        );
    }

    private function startOfDay(string $day): DateTimeImmutable
    {
        return new DateTimeImmutable($day . ' 00:00:00', new DateTimeZone('UTC'));
    }

    private function endOfDay(string $day): DateTimeImmutable
    {
        return new DateTimeImmutable($day . ' 23:59:59', new DateTimeZone('UTC'));
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    private static function assertContainsOrderId(int $id, array $items): void
    {
        $ids = array_map(static fn (array $row): int => (int) $row['id'], $items);
        self::assertContains($id, $ids, "Expected order #{$id} to be included but it was not.");
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    private static function assertNotContainsOrderId(int $id, array $items): void
    {
        $ids = array_map(static fn (array $row): int => (int) $row['id'], $items);
        self::assertNotContains($id, $ids, "Expected order #{$id} to be excluded but it was present.");
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

        return $pdo;
    }

    private function seedOrder(int $id, string $createdAt, int $userId = self::USER_ID): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO orders
                (id, user_id, created_by_user_id, room_id, status, notes, total_amount, created_at, updated_at, cancelled_at)
             VALUES
                (:id, :user_id, :user_id, 1, "PROCESSING", NULL, "10.00", :created_at, :created_at, NULL)'
        );

        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
            'created_at' => $createdAt,
        ]);
    }
}