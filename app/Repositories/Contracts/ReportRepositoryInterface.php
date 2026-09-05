<?php

declare(strict_types=1);

namespace Cafeteria\Repositories\Contracts;

interface ReportRepositoryInterface
{
    /**
     * Per-user totals for the checks summary report.
     *
     * When $perPage is null, all matching user rows are returned (export / aggregates).
     * When set, results are limited with page/per_page metadata.
     *
     * @param array{
     *     from?: string|null,
     *     to?: string|null,
     *     user_id?: int|null,
     *     include_cancelled?: bool
     * } $filter
     *
     * @return array{
     *     users: list<array{
     *         user_id: int|string,
     *         user_name: string,
     *         order_count: int|string,
     *         total_amount: string|float|int
     *     }>,
     *     total: int,
     *     page: int,
     *     per_page: int,
     *     total_orders: int,
     *     total_amount: string
     * }
     */
    public function summarize(array $filter, int $page = 1, ?int $perPage = null): array;

    /**
     * Orders for a single user within validated report filters.
     *
     * When $perPage is null, all matching orders are returned.
     *
     * @param array{
     *     from?: string|null,
     *     to?: string|null,
     *     include_cancelled?: bool
     * } $filter
     *
     * @return array{
     *     items: list<array<string, mixed>>,
     *     total: int,
     *     page: int,
     *     per_page: int
     * }
     */
    public function ordersForUser(
        int $userId,
        array $filter,
        int $page = 1,
        ?int $perPage = null,
    ): array;

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
