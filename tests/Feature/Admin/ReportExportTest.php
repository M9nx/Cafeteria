<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Cafeteria\Core\Http\Response;
use Cafeteria\DTO\ChecksFilter;
use Cafeteria\Repositories\Contracts\ReportRepositoryInterface;
use Cafeteria\Services\ReportExportService;
use Cafeteria\Services\ReportQueryService;
use Cafeteria\Validation\ChecksFilterValidator;
use DateTimeZone;
use PDO;
use Tests\Support\HttpTestCase;

final class ReportExportTest extends HttpTestCase
{
    public function test_guest_is_redirected_to_login_from_export(): void
    {
        $response = $this->get('/admin/checks/export');

        self::assertSame(302, $this->responseStatus($response));
        self::assertSame(
            '/login',
            $this->responseHeader($response, 'Location')
        );
    }

    public function test_regular_user_is_forbidden_from_export(): void
    {
        $this->loginAsUser();

        $response = $this->get('/admin/checks/export');

        self::assertSame(403, $this->responseStatus($response));
        self::assertStringContainsString(
            'Forbidden',
            $this->responseContent($response)
        );
    }

    public function test_admin_can_export_csv_with_safe_headers(): void
    {
        $this->loginAsAdmin();

        $response = $this->getWithQuery(
            '/admin/checks/export?from=2026-03-01&to=2026-03-10&user_id=1',
            [
                'from' => '2026-03-01',
                'to' => '2026-03-10',
                'user_id' => '1',
            ],
        );

        self::assertSame(200, $this->responseStatus($response));
        self::assertSame(
            'text/csv; charset=UTF-8',
            $this->responseHeader($response, 'Content-Type')
        );
        self::assertSame(
            'attachment; filename="checks-report.csv"',
            $this->responseHeader($response, 'Content-Disposition')
        );
        self::assertSame(
            'no-store, no-cache, must-revalidate',
            $this->responseHeader($response, 'Cache-Control')
        );

        $csv = $this->responseContent($response);

        self::assertStringStartsWith("\xEF\xBB\xBF", $csv);
        self::assertStringContainsString('"User ID",User,Orders,"Total amount"', $csv);
    }

    public function test_csv_formula_prefixes_are_neutralized(): void
    {
        $exporter = new ReportExportService(
            new ReportQueryService(
                new FormulaStubReportRepository(),
                new ChecksFilterValidator(
                    $this->sqliteUsers(),
                    new DateTimeZone('Africa/Cairo'),
                ),
            ),
        );

        $csv = $this->responseContent(
            $exporter->export(new ChecksFilter())
        );

        self::assertStringContainsString("'=1+1", $csv);
        self::assertStringContainsString("'+100", $csv);
        self::assertStringContainsString("'-50.00", $csv);
        self::assertStringContainsString("'@SUM(A1)", $csv);
        self::assertStringContainsString('Safe User', $csv);
        self::assertStringNotContainsString("'Safe User", $csv);
    }

    public function test_invalid_export_filters_return_safe_html_not_csv(): void
    {
        $this->loginAsAdmin();

        $response = $this->getWithQuery(
            '/admin/checks/export?from=not-a-date',
            [
                'from' => 'not-a-date',
            ],
        );

        self::assertSame(200, $this->responseStatus($response));
        self::assertNotSame(
            'text/csv; charset=UTF-8',
            $this->responseHeader($response, 'Content-Type')
        );

        $content = $this->responseContent($response);

        self::assertStringContainsString('Checks report', $content);
        self::assertStringContainsString(
            'From date must be in YYYY-MM-DD format.',
            $content
        );
        self::assertStringNotContainsString('/admin/checks/export', $content);
    }

    /**
     * @param array<string, mixed> $query
     */
    private function getWithQuery(string $path, array $query): Response
    {
        $_GET = $query;

        return $this->get($path);
    }

    private function sqliteUsers(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec(
            'CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, email TEXT)'
        );

        return $pdo;
    }
}

/** @implements ReportRepositoryInterface */
final class FormulaStubReportRepository implements ReportRepositoryInterface
{
    public function summarize(array $filters): array
    {
        return [
            'users' => [
                [
                    'user_id' => 10,
                    'user_name' => '=1+1',
                    'order_count' => 1,
                    'total_amount' => '+100',
                ],
                [
                    'user_id' => 11,
                    'user_name' => '@SUM(A1)',
                    'order_count' => 1,
                    'total_amount' => '-50.00',
                ],
                [
                    'user_id' => 12,
                    'user_name' => 'Safe User',
                    'order_count' => 2,
                    'total_amount' => '40.00',
                ],
            ],
        ];
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
        return null;
    }
}
