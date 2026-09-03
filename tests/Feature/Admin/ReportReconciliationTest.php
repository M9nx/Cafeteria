<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Cafeteria\DTO\ChecksFilter;
use Cafeteria\Repositories\Pdo\PdoReportRepository;
use Cafeteria\Services\ReportQueryService;
use Cafeteria\Validation\ChecksFilterValidator;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\Support\ReportOrdersFixture;

final class ReportReconciliationTest extends TestCase
{
    private PDO $pdo;

    private ReportOrdersFixture $fixture;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdo = $this->freshDatabase();

        $this->fixture = new ReportOrdersFixture($this->pdo);
        $this->fixture->generate();
    }

    public function test_default_summary_reconciles_fixture_totals_per_user(): void
    {
        $service = $this->makeService();

        $summary = $service->summarize(
            new ChecksFilter()
        );

        self::assertArrayHasKey('users', $summary);

        $users = $summary['users'];

        self::assertCount(2, $users);

        $byUserId = [];

        foreach ($users as $user) {
            $byUserId[(int) $user['user_id']] = $user;
        }

        self::assertArrayHasKey($this->fixture->userAId, $byUserId);
        self::assertArrayHasKey($this->fixture->userBId, $byUserId);

        self::assertSame(
            5,
            (int) $byUserId[$this->fixture->userAId]['order_count']
        );

        self::assertSame(
            '400.00',
            number_format(
                (float) $byUserId[$this->fixture->userAId]['total_amount'],
                2,
                '.',
                ''
            )
        );

        self::assertSame(
            1,
            (int) $byUserId[$this->fixture->userBId]['order_count']
        );

        self::assertSame(
            '150.00',
            number_format(
                (float) $byUserId[$this->fixture->userBId]['total_amount'],
                2,
                '.',
                ''
            )
        );
    }

    public function test_date_filtered_summary_includes_both_boundaries(): void
    {
        $service = $this->makeService();

        $summary = $service->summarize(
            new ChecksFilter(
                from: '2026-03-01',
                to: '2026-03-01',
            )
        );

        self::assertArrayHasKey('users', $summary);

        $users = $summary['users'];

        self::assertCount(2, $users);

        $byUserId = [];

        foreach ($users as $user) {
            $byUserId[(int) $user['user_id']] = $user;
        }

        self::assertSame(
            3,
            (int) $byUserId[$this->fixture->userAId]['order_count']
        );

        self::assertSame(
            '300.00',
            number_format(
                (float) $byUserId[$this->fixture->userAId]['total_amount'],
                2,
                '.',
                ''
            )
        );

        self::assertSame(
            1,
            (int) $byUserId[$this->fixture->userBId]['order_count']
        );

        self::assertSame(
            '150.00',
            number_format(
                (float) $byUserId[$this->fixture->userBId]['total_amount'],
                2,
                '.',
                ''
            )
        );
    }

    public function test_include_cancelled_adds_cancelled_order_to_summary(): void
    {
        $service = $this->makeService();

        $summary = $service->summarize(
            new ChecksFilter(
                from: '2026-03-01',
                to: '2026-03-01',
                includeCancelled: true,
            )
        );

        self::assertArrayHasKey('users', $summary);

        $users = $summary['users'];

        $byUserId = [];

        foreach ($users as $user) {
            $byUserId[(int) $user['user_id']] = $user;
        }

        self::assertSame(
            4,
            (int) $byUserId[$this->fixture->userAId]['order_count']
        );

        self::assertSame(
            '800.00',
            number_format(
                (float) $byUserId[$this->fixture->userAId]['total_amount'],
                2,
                '.',
                ''
            )
        );
    }

    public function test_user_filter_returns_only_selected_user(): void
    {
        $service = $this->makeService();

        $summary = $service->summarize(
            new ChecksFilter(
                userId: $this->fixture->userAId,
            )
        );

        self::assertArrayHasKey('users', $summary);

        $users = $summary['users'];

        self::assertCount(1, $users);

        self::assertSame(
            $this->fixture->userAId,
            (int) $users[0]['user_id']
        );

        self::assertSame(
            5,
            (int) $users[0]['order_count']
        );

        self::assertSame(
            '400.00',
            number_format(
                (float) $users[0]['total_amount'],
                2,
                '.',
                ''
            )
        );
    }

    private function makeService(): ReportQueryService
    {
        return new ReportQueryService(
            new PdoReportRepository($this->pdo),
            new ChecksFilterValidator(
                $this->pdo,
                new \DateTimeZone('Africa/Cairo'),
            ),
        );
    }

    private function freshDatabase(): PDO
    {
        $pdo = new PDO('sqlite::memory:');

        $pdo->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );

        $pdo->exec('
            CREATE TABLE rooms (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL
            )
        ');

        $pdo->exec('
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                password TEXT NOT NULL
            )
        ');

        $pdo->exec('
            CREATE TABLE orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                room_id INTEGER NOT NULL,
                status TEXT NOT NULL,
                total_amount DECIMAL(10,2) NOT NULL,
                notes TEXT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                cancelled_at DATETIME NULL
            )
        ');

        $pdo->exec('
            CREATE TABLE order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                product_name_snapshot TEXT NOT NULL,
                unit_price_snapshot DECIMAL(10,2) NOT NULL,
                quantity INTEGER NOT NULL,
                line_total DECIMAL(10,2) NOT NULL
            )
        ');

        return $pdo;
    }
}