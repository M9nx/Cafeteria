<?php

declare(strict_types=1);

namespace Cafeteria\Controllers\Admin;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\DTO\ChecksFilter;
use Cafeteria\Services\ReportQueryService;
use InvalidArgumentException;

final class ReportController
{
    use RendersAdminView;

    public function __construct(
        private readonly ReportQueryService $reports,
    ) {
    }

    public function index(
        Request $request,
        AuthenticatedUser $admin,
    ): Response {
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
                'filters' => [
                    'user_id' => $userId ?? '',
                    'from' => $from,
                    'to' => $to,
                    'include_cancelled' => $includeCancelled,
                ],
                'errors' => $errors,
            ],
        );
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
