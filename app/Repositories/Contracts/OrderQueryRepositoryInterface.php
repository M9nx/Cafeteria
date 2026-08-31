<?php

declare(strict_types=1);

namespace Cafeteria\Repositories\Contracts;

use DateTimeImmutable;

interface OrderQueryRepositoryInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function findLatestForUser(int $userId): ?array;

    /**
     * @return array{items: list<array<string, mixed>>, total: int, page: int, per_page: int}
     */
    public function paginateForUser(
        int $userId,
        ?DateTimeImmutable $from,
        ?DateTimeImmutable $to,
        int $page = 1,
        int $perPage = 15
    ): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findOwnedDetail(int $orderId, int $userId): ?array;

    /**
     * @return array<string, mixed>|null
     */
    public function findDetailForAdmin(int $orderId): ?array;

    /**
     * @return array{items: list<array<string, mixed>>, total: int, page: int, per_page: int}
     */
    public function listCurrentQueue(int $page = 1, int $perPage = 15): array;
}
