<?php

declare(strict_types=1);

namespace Cafeteria\Repositories\Contracts;

interface ProductRepositoryInterface
{
    /**
     * @return array{items: list<array<string, mixed>>, total: int, page: int, per_page: int}
     */
    public function paginate(int $page = 1, int $perPage = 15): array;

    /**
    * @return array{
    *     items: list<array<string, mixed>>,
    *     total: int,
    *     page: int,
    *     per_page: int
    * }
    */
   public function paginateAvailable(
       int $page = 1,
       int $perPage = 15
   ): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array;

    /**
     * @param list<int> $ids
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAvailableByIds(array $ids): array;

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): int;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(int $id, array $attributes): bool;

    public function softDelete(int $id): bool;
}
