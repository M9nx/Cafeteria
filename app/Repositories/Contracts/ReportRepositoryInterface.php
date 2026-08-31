<?php

declare(strict_types=1);

namespace Cafeteria\Repositories\Contracts;

interface ReportRepositoryInterface
{
    /**
     * @param array{from?: string, to?: string, user_id?: int, include_cancelled?: bool} $filter
     *
     * @return array<string, mixed>
     */
    public function summarize(array $filter): array;

    /**
     * @param array{from?: string, to?: string, include_cancelled?: bool} $filter
     *
     * @return list<array<string, mixed>>
     */
    public function ordersForUser(int $userId, array $filter): array;

    /**
     * @param array{from?: string, to?: string, include_cancelled?: bool} $filter
     *
     * @return array<string, mixed>|null
     */
    public function orderDetailsForReport(int $orderId, array $filter): ?array;
}
