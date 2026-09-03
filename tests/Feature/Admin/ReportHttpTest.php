<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Cafeteria\Core\Http\Response;
use Tests\Support\HttpTestCase;

final class ReportHttpTest extends HttpTestCase
{
    public function test_admin_can_open_checks_report_with_valid_filters(): void
    {
        $this->loginAsAdmin();

        $response = $this->getWithQuery(
            '/admin/checks?from=2026-03-01&to=2026-03-10&user_id=1&include_cancelled=1',
            [
                'from' => '2026-03-01',
                'to' => '2026-03-10',
                'user_id' => '1',
                'include_cancelled' => '1',
            ],
        );

        self::assertSame(200, $this->responseStatus($response));

        $content = $this->responseContent($response);

        self::assertStringContainsString('Checks report', $content);
        self::assertStringContainsString('value="2026-03-01"', $content);
        self::assertStringContainsString('value="2026-03-10"', $content);
        self::assertStringContainsString('value="1"', $content);
        self::assertStringContainsString('checked', $content);
        self::assertStringContainsString(
            '/admin/checks/export?user_id=1&amp;from=2026-03-01&amp;to=2026-03-10&amp;include_cancelled=1',
            $content
        );
    }

    public function test_admin_can_open_user_drill_down_with_valid_filters(): void
    {
        $this->loginAsAdmin();

        $response = $this->getWithQuery(
            '/admin/checks/users/1?from=2026-03-01&to=2026-03-10&include_cancelled=1',
            [
                'from' => '2026-03-01',
                'to' => '2026-03-10',
                'include_cancelled' => '1',
            ],
        );

        self::assertSame(200, $this->responseStatus($response));

        $content = $this->responseContent($response);

        self::assertStringContainsString('Checks drill-down', $content);
        self::assertStringContainsString(
            '/admin/checks?from=2026-03-01&amp;to=2026-03-10&amp;include_cancelled=1',
            $content
        );
    }

    public function test_guest_is_redirected_to_login_from_report_index(): void
    {
        $response = $this->get(
            '/admin/checks?from=2026-03-01&to=2026-03-10'
        );

        self::assertSame(302, $this->responseStatus($response));

        self::assertSame(
            '/login',
            $this->responseHeader($response, 'Location')
        );
    }

    public function test_guest_is_redirected_to_login_from_report_drill_down(): void
    {
        $response = $this->get(
            '/admin/checks/users/1?from=2026-03-01&to=2026-03-10'
        );

        self::assertSame(302, $this->responseStatus($response));

        self::assertSame(
            '/login',
            $this->responseHeader($response, 'Location')
        );
    }

    public function test_regular_user_is_forbidden_from_report_index(): void
    {
        $this->loginAsUser();

        $response = $this->get(
            '/admin/checks?from=2026-03-01&to=2026-03-10'
        );

        self::assertSame(403, $this->responseStatus($response));

        self::assertStringContainsString(
            'Forbidden',
            $this->responseContent($response)
        );
    }

    public function test_regular_user_is_forbidden_from_report_drill_down(): void
    {
        $this->loginAsUser();

        $response = $this->get(
            '/admin/checks/users/1?from=2026-03-01&to=2026-03-10'
        );

        self::assertSame(403, $this->responseStatus($response));

        self::assertStringContainsString(
            'Forbidden',
            $this->responseContent($response)
        );
    }

    public function test_unknown_report_drill_down_user_returns_safe_error_page(): void
    {
        $this->loginAsAdmin();

        $response = $this->get('/admin/checks/users/999999');

        self::assertNotSame(500, $this->responseStatus($response));

        self::assertStringContainsString(
            'The selected user does not exist.',
            $this->responseContent($response)
        );
    }

    public function test_invalid_report_filter_does_not_produce_server_error(): void
    {
        $this->loginAsAdmin();

        $response = $this->getWithQuery(
            '/admin/checks?from=not-a-date',
            [
                'from' => 'not-a-date',
            ],
        );

        self::assertNotSame(500, $this->responseStatus($response));
        self::assertSame(200, $this->responseStatus($response));
        self::assertStringContainsString(
            'From date must be in YYYY-MM-DD format.',
            $this->responseContent($response)
        );
        self::assertStringNotContainsString(
            '/admin/checks/export',
            $this->responseContent($response)
        );
    }

    public function test_admin_can_export_csv_with_preserved_filters(): void
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
        self::assertStringContainsString(
            '"User ID",User,Orders,"Total amount"',
            $this->responseContent($response)
        );
    }

    public function test_unknown_admin_report_path_is_not_found(): void
    {
        $this->loginAsAdmin();

        $response = $this->get('/admin/checks/missing');

        self::assertSame(404, $this->responseStatus($response));
        self::assertStringContainsString(
            'Not Found',
            $this->responseContent($response)
        );
    }

    /**
     * @param array<string, mixed> $query
     */
    private function getWithQuery(string $path, array $query): Response
    {
        $_GET = $query;

        return $this->get($path);
    }
}