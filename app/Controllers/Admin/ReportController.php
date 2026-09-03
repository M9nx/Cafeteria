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
        [$filter, $filters] = $this->buildFilter($request);

        $errors = [];
        $summary = ['users' => []];

        try {
            $summary = $this->reports->summarize($filter);
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
        [$filter] = $this->buildFilter($request);

        return $this->exporter->export($filter);
    }

    public function userDrillDown(
        Request $request,
        AuthenticatedUser $admin,
        int $id,
    ): Response {
        [$filter, $filters] = $this->buildFilter($request);

        $errors = [];
        $drillDown = [
            'user' => [
                'id' => $id,
                'name' => '',
                'email' => '',
            ],
            'orders' => [],
            'summary' => [
                'order_count' => 0,
                'total_amount' => '0.00',
            ],
        ];

        try {
            $drillDown = $this->reports->drillDown($id, $filter);
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
        $from = trim((string) $request->input('from', ''));
        $to = trim((string) $request->input('to', ''));
        $includeCancelled = (string) $request->input('include_cancelled', '') === '1';

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

    private function optionalUserId(mixed $value): ?int
    {
        if (!is_string($value) && !is_int($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        $userId = (int) $normalized;

        return $userId > 0 ? $userId : null;
    }
}