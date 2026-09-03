<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Cafeteria\DTO\ChecksFilter;
use Cafeteria\Repositories\Contracts\ReportRepositoryInterface;
use Cafeteria\Services\ReportQueryService;
use Cafeteria\Validation\ChecksFilterValidator;
use DateTimeZone;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;

final class ReportQueryServiceTest extends TestCase
{
    public function test_summarize_passes_filter_to_repository(): void
    {
        $repository = new RecordingReportRepository();
        $service = new ReportQueryService(
            $repository,
            new ChecksFilterValidator($this->pdo(), new DateTimeZone('UTC')),
        );

        $filter = new ChecksFilter(
            userId: null,
            from: '2026-01-01',
            to: '2026-01-31',
            includeCancelled: false,
        );

        $result = $service->summarize($filter);

        self::assertSame(
            [
                'from' => '2026-01-01',
                'to' => '2026-01-31',
                'user_id' => null,
                'include_cancelled' => false,
            ],
            $repository->lastSummarizeFilters,
        );
        self::assertSame(['users' => []], $result);
    }

    public function test_summarize_raises_when_validation_fails(): void
    {
        $service = new ReportQueryService(
            new RecordingReportRepository(),
            new ChecksFilterValidator($this->pdo(), new DateTimeZone('UTC')),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('From date must not be after to date.');

        $service->summarize(new ChecksFilter(
            from: '2026-02-01',
            to: '2026-01-01',
        ));
    }

    public function test_summarize_rejects_unknown_user(): void
    {
        $service = new ReportQueryService(
            new RecordingReportRepository(),
            new ChecksFilterValidator($this->pdo(), new DateTimeZone('UTC')),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The selected user does not exist.');

        $service->summarize(new ChecksFilter(userId: 99));
    }

    public function test_drill_down_returns_user_orders_and_summary(): void
    {
        $repository = new RecordingReportRepository();
        $service = new ReportQueryService(
            $repository,
            new ChecksFilterValidator($this->pdo(), new \DateTimeZone('UTC')),
        );

        $result = $service->drillDown(
            1,
            new ChecksFilter(
                from: '2026-01-01',
                to: '2026-01-31',
            ),
        );

        self::assertSame(1, $result['user']['id']);
        self::assertSame('Report User', $result['user']['name']);
        self::assertSame([], $result['orders']);
        self::assertSame(0, $result['summary']['order_count']);
    }

    private function pdo(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, email TEXT)');
        $pdo->exec(
            "INSERT INTO users (id, name, email) VALUES (1, 'Report User', 'report-user@example.test')"
        );

        return $pdo;
    }
}

/** @implements ReportRepositoryInterface */
final class RecordingReportRepository implements ReportRepositoryInterface
{
    /** @var array<string, mixed>|null */
    public ?array $lastSummarizeFilters = null;

    public function summarize(array $filters): array
    {
        $this->lastSummarizeFilters = $filters;

        return ['users' => []];
    }

    public function ordersForUser(int $userId, array $filters): array
    {
        return [];
    }

    public function orderDetailsForReport(int $orderId, array $filters): ?array
    {
        return null;
    }

    public function findReportUser(int $userId): ?array
    {
        return [
            'id' => $userId,
            'name' => 'Report User',
            'email' => 'report-user@example.test',
        ];
    }
}
