<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Cafeteria\Core\Http\Response;
use Tests\Support\HttpTestCase;

final class ReportSecurityTest extends HttpTestCase
{
    public function test_guest_is_redirected_to_login_from_checks_report(): void
    {
        $response = $this->get('/admin/checks');

        self::assertSame(
            302,
            $this->responseStatus($response)
        );

        self::assertSame(
            '/login',
            $this->responseHeader($response, 'Location')
        );
    }

    public function test_guest_is_redirected_to_login_from_user_drill_down(): void
    {
        $response = $this->get('/admin/checks/users/2');

        self::assertSame(
            302,
            $this->responseStatus($response)
        );

        self::assertSame(
            '/login',
            $this->responseHeader($response, 'Location')
        );
    }

    public function test_regular_user_is_forbidden_from_checks_report(): void
    {
        $this->loginAsUser();

        $response = $this->get('/admin/checks');

        self::assertSame(
            403,
            $this->responseStatus($response)
        );

        self::assertStringContainsString(
            'Forbidden',
            $this->responseContent($response)
        );
    }

    public function test_regular_user_is_forbidden_from_user_drill_down(): void
    {
        $this->loginAsUser();

        $response = $this->get('/admin/checks/users/1');

        self::assertSame(
            403,
            $this->responseStatus($response)
        );

        self::assertStringContainsString(
            'Forbidden',
            $this->responseContent($response)
        );
    }

    public function test_admin_rejects_invalid_from_date_safely(): void
    {
        $this->loginAsAdmin();

        $response = $this->getWithQuery(
            '/admin/checks?from=not-a-date',
            [
                'from' => 'not-a-date',
            ],
        );

        self::assertNotSame(
            500,
            $this->responseStatus($response)
        );

        $content = $this->responseContent($response);

        self::assertStringContainsString(
            'From date must be in YYYY-MM-DD format.',
            $content
        );
    }

    public function test_admin_rejects_invalid_to_date_safely(): void
    {
        $this->loginAsAdmin();

        $response = $this->getWithQuery(
            '/admin/checks?to=2026-99-99',
            [
                'to' => '2026-99-99',
            ],
        );

        self::assertNotSame(
            500,
            $this->responseStatus($response)
        );

        $content = $this->responseContent($response);

        self::assertStringContainsString(
            'To date must be in YYYY-MM-DD format.',
            $content
        );
    }

    public function test_admin_rejects_reversed_date_range_safely(): void
    {
        $this->loginAsAdmin();

        $response = $this->getWithQuery(
            '/admin/checks?from=2026-03-10&to=2026-03-01',
            [
                'from' => '2026-03-10',
                'to' => '2026-03-01',
            ],
        );

        self::assertNotSame(
            500,
            $this->responseStatus($response)
        );

        $content = $this->responseContent($response);

        self::assertStringContainsString(
            'From date must not be after to date.',
            $content
        );
    }

    public function test_admin_rejects_unknown_user_id_safely(): void
    {
        $this->loginAsAdmin();

        $response = $this->getWithQuery(
            '/admin/checks?user_id=999999',
            [
                'user_id' => '999999',
            ],
        );

        self::assertNotSame(
            500,
            $this->responseStatus($response)
        );

        $content = $this->responseContent($response);

        self::assertStringContainsString(
            'The selected user does not exist.',
            $content
        );
    }

    public function test_admin_cannot_use_malicious_user_id_to_bypass_filter(): void
    {
        $this->loginAsAdmin();

        $response = $this->getWithQuery(
            '/admin/checks?user_id=1%20OR%201=1',
            [
                'user_id' => '1 OR 1=1',
            ],
        );

        self::assertNotSame(
            500,
            $this->responseStatus($response)
        );

        $content = $this->responseContent($response);

        self::assertStringContainsString('Checks report', $content);
        self::assertStringContainsString('User ID must be valid.', $content);
        self::assertStringNotContainsString('SQLSTATE', $content);
    }

    public function test_malformed_user_id_is_rejected_safely(): void
    {
        $this->loginAsAdmin();

        foreach (['abc', '1abc', '0', '-4'] as $userId) {
            $response = $this->getWithQuery(
                '/admin/checks?user_id=' . rawurlencode($userId),
                [
                    'user_id' => $userId,
                ],
            );

            self::assertSame(
                200,
                $this->responseStatus($response),
                'Malformed user_id=' . $userId . ' must not 500.'
            );

            $content = $this->responseContent($response);

            self::assertStringContainsString('Checks report', $content);
            self::assertStringContainsString('User ID must be valid.', $content);
            self::assertStringNotContainsString('SQLSTATE', $content);
            self::assertStringNotContainsString('/admin/checks/export', $content);
        }
    }

    public function test_array_shaped_include_cancelled_is_rejected_safely(): void
    {
        $this->loginAsAdmin();

        $response = $this->getWithQuery(
            '/admin/checks?include_cancelled[]=1',
            [
                'include_cancelled' => ['1'],
            ],
        );

        self::assertSame(200, $this->responseStatus($response));

        $content = $this->responseContent($response);

        self::assertStringContainsString('Checks report', $content);
        self::assertStringContainsString(
            'Include cancelled must be a valid flag.',
            $content
        );
        self::assertStringNotContainsString('/admin/checks/export', $content);
    }

    public function test_xss_filter_payload_is_escaped_on_checks_report(): void
    {
        $this->loginAsAdmin();

        $payload = '<script>alert(1)</script>';

        $response = $this->getWithQuery(
            '/admin/checks?from=' . rawurlencode($payload),
            [
                'from' => $payload,
            ],
        );

        self::assertNotSame(500, $this->responseStatus($response));

        $content = $this->responseContent($response);

        self::assertStringNotContainsString($payload, $content);
        self::assertStringContainsString(
            'From date must be in YYYY-MM-DD format.',
            $content
        );
    }

    public function test_admin_cannot_access_nonexistent_user_drill_down(): void
    {
        $this->loginAsAdmin();

        $response = $this->get(
            '/admin/checks/users/999999'
        );

        self::assertNotSame(
            500,
            $this->responseStatus($response)
        );

        $content = $this->responseContent($response);

        self::assertStringContainsString(
            'The selected user does not exist.',
            $content
        );
    }

    public function test_admin_drill_down_does_not_expose_another_user_through_filter(): void
    {
        $this->loginAsAdmin();

        $response = $this->getWithQuery(
            '/admin/checks/users/2?user_id=1',
            [
                'user_id' => '1',
            ],
        );

        self::assertNotSame(
            500,
            $this->responseStatus($response)
        );

        $content = $this->responseContent($response);

        self::assertStringContainsString(
            'Checks drill-down',
            $content
        );

        // Path user is #2 (Demo User). The logged-in admin name appears in the
        // navbar, so assert the drill-down subject itself was not swapped by
        // the conflicting user_id filter.
        self::assertMatchesRegularExpression(
            '/User details.*?Name<\/dt>\s*<dd class="col-sm-9">\s*Demo User/s',
            $content
        );
    }

    /**
     * @param array<string, mixed> $query
     */
    private function getWithQuery(
        string $path,
        array $query
    ): Response {
        $_GET = $query;

        return $this->get($path);
    }
}

