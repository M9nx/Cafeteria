<?php

declare(strict_types=1);

namespace Cafeteria\Controllers\Admin;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\DTO\ChecksFilter;
use Cafeteria\Services\ReportExportService;
use Cafeteria\Services\ReportQueryService;
use InvalidArgumentException;

final class ReportController
{
    use RendersAdminView;

    public function __construct(
        private readonly ReportQueryService $reports,
        private readonly ReportExportService $exporter,
    ) {
    }

    public function index(
        Request $request,
        AuthenticatedUser $admin,
    ): Response {
        $filters = $this->rawFilters($request);
        $errors = [];
        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(1, min(50, (int) $request->input('per_page', 15)));
        $summary = [
            'users' => [],
            'total' => 0,
            'page' => $page,
            'per_page' => $perPage,
            'total_orders' => 0,
            'total_amount' => '0.00',
        ];

        try {
            [$filter, $filters] = $this->buildFilter($request);
            $summary = $this->reports->summarize($filter, $page, $perPage);
        } catch (InvalidArgumentException $exception) {
            $errors = [$exception->getMessage()];
        }

        return $this->renderAdmin(
            $admin,
            'admin.reports.index',
            'Checks report',
            [
                'summary' => $summary,
                'filters' => $filters,
                'errors' => $errors,
            ],
        );
    }

    public function export(
        Request $request,
        AuthenticatedUser $admin,
    ): Response {
        try {
            [$filter] = $this->buildFilter($request);

            return $this->exporter->export($filter);
        } catch (InvalidArgumentException $exception) {
            return $this->renderAdmin(
                $admin,
                'admin.reports.index',
                'Checks report',
                [
                    'summary' => ['users' => []],
                    'filters' => $this->rawFilters($request),
                    'errors' => [$exception->getMessage()],
                ],
            );
        }
    }

    public function userDrillDown(
        Request $request,
        AuthenticatedUser $admin,
        int $id,
    ): Response {
        $filters = $this->rawFilters($request);
        $errors = [];
        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(1, min(50, (int) $request->input('per_page', 15)));
        $drillDown = [
            'user' => [
                'id' => $id,
                'name' => '',
                'email' => '',
            ],
            'orders' => [
                'items' => [],
                'total' => 0,
                'page' => $page,
                'per_page' => $perPage,
            ],
            'summary' => [
                'order_count' => 0,
                'total_amount' => '0.00',
            ],
        ];

        try {
            [$filter, $filters] = $this->buildFilter($request);
            $drillDown = $this->reports->drillDown($id, $filter, $page, $perPage);
        } catch (InvalidArgumentException $exception) {
            $errors = [$exception->getMessage()];
        }

        return $this->renderAdmin(
            $admin,
            'admin.reports.user',
            'Checks drill-down',
            [
                'drillDown' => $drillDown,
                'filters' => $filters,
                'errors' => $errors,
            ],
        );
    }

    /**
     * @return array{0: ChecksFilter, 1: array<string, mixed>}
     */
    private function buildFilter(Request $request): array
    {
        $userId = $this->optionalUserId($request->input('user_id'));
        $from = $this->scalarQueryValue(
            $request->input('from', ''),
            'From date must be in YYYY-MM-DD format.',
        );
        $to = $this->scalarQueryValue(
            $request->input('to', ''),
            'To date must be in YYYY-MM-DD format.',
        );
        $includeCancelled = $this->includeCancelledFlag(
            $request->input('include_cancelled', ''),
        );

        $filter = new ChecksFilter(
            userId: $userId,
            from: $from !== '' ? $from : null,
            to: $to !== '' ? $to : null,
            includeCancelled: $includeCancelled,
        );

        return [
            $filter,
            [
                'user_id' => $userId ?? '',
                'from' => $from,
                'to' => $to,
                'include_cancelled' => $includeCancelled,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function rawFilters(Request $request): array
    {
        $userId = $request->input('user_id');
        $from = $request->input('from');
        $to = $request->input('to');
        $includeCancelled = $request->input('include_cancelled');

        return [
            'user_id' => is_scalar($userId) ? (string) $userId : '',
            'from' => is_scalar($from) ? (string) $from : '',
            'to' => is_scalar($to) ? (string) $to : '',
            'include_cancelled' => is_scalar($includeCancelled)
                && (string) $includeCancelled === '1',
        ];
    }

    private function optionalUserId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value) && !is_int($value)) {
            throw new InvalidArgumentException('User ID must be valid.');
        }

        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        if (preg_match('/^[1-9][0-9]*$/', $normalized) !== 1) {
            throw new InvalidArgumentException('User ID must be valid.');
        }

        return (int) $normalized;
    }

    private function scalarQueryValue(mixed $value, string $message): string
    {
        if ($value === null) {
            return '';
        }

        if (!is_scalar($value)) {
            throw new InvalidArgumentException($message);
        }

        return trim((string) $value);
    }

    private function includeCancelledFlag(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        if (!is_scalar($value)) {
            throw new InvalidArgumentException(
                'Include cancelled must be a valid flag.'
            );
        }

        return (string) $value === '1';
    }
}
