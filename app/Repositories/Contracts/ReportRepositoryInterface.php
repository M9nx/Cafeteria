<?php

declare(strict_types=1);

namespace Cafeteria\Repositories\Contracts;

interface ReportRepositoryInterface
{
    /**
     * Per-user totals for the checks summary report.
     *
     * @param array{
     *     from?: string|null,
     *     to?: string|null,
     *     user_id?: int|null,
     *     include_cancelled?: bool
     * } $filter
     *
     * @return array{users: list<array{
     *     user_id: int|string,
     *     user_name: string,
     *     order_count: int|string,
     *     total_amount: string|float|int
     * }>}
     */
    public function summarize(array $filter): array;

    /**
     * Orders for a single user within validated report filters.
     *
     * @param array{
     *     from?: string|null,
     *     to?: string|null,
     *     include_cancelled?: bool
     * } $filter
     *
     * @return list<array<string, mixed>>
     */
    public function ordersForUser(int $userId, array $filter): array;

    /**
     * Single order detail for report drill-down when the order matches filters.
     *
     * @param array{
     *     from?: string|null,
     *     to?: string|null,
     *     include_cancelled?: bool
     * } $filter
     *
     * @return array<string, mixed>|null
     */
    public function orderDetailsForReport(int $orderId, array $filter): ?array;

    /**
     * @return array{id: int|string, name: string, email: string}|null
     */
    public function findReportUser(int $userId): ?array;
}
