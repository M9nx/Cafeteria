<?php

declare(strict_types=1);

namespace Cafeteria\Repositories\Contracts;

interface RoomRepositoryInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listActive(): array;

    /**
     * Active rooms plus an optional inactive room (for edit forms).
     *
     * @return list<array<string, mixed>>
     */
    public function listForAssignment(?int $includeRoomId = null): array;

    /**
     * @return array{items: list<array<string, mixed>>, total: int, page: int, per_page: int}
     */
    public function paginate(int $page = 1, int $perPage = 15): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array;

    public function create(string $name): int;

    public function update(int $id, string $name, bool $isActive = true): bool;

    public function deactivate(int $id): bool;
}
