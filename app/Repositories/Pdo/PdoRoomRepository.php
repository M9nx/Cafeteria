<?php

declare(strict_types=1);

namespace Cafeteria\Repositories\Pdo;

use Cafeteria\Repositories\Contracts\RoomRepositoryInterface;
use PDO;

final class PdoRoomRepository implements RoomRepositoryInterface
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listActive(): array
    {
        $sql = "
            SELECT
                id,
                name,
                is_active,
                created_at,
                updated_at
            FROM rooms
            WHERE is_active = 1
            ORDER BY name ASC, id ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listForAssignment(?int $includeRoomId = null): array
    {
        $rooms = $this->listActive();

        if ($includeRoomId === null || $includeRoomId <= 0) {
            return $rooms;
        }

        foreach ($rooms as $room) {
            if ((int) ($room['id'] ?? 0) === $includeRoomId) {
                return $rooms;
            }
        }

        $extra = $this->findById($includeRoomId);

        if ($extra !== null) {
            $rooms[] = $extra;
        }

        return $rooms;
    }

    /**
     * @return array{
     *     items: list<array<string, mixed>>,
     *     total: int,
     *     page: int,
     *     per_page: int
     * }
     */
    public function paginate(
        int $page = 1,
        int $perPage = 15
    ): array {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        $countStmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM rooms'
        );

        $countStmt->execute();

        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT
                id,
                name,
                is_active,
                created_at,
                updated_at
            FROM rooms
            ORDER BY name ASC, id ASC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':limit',
            $perPage,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':offset',
            $offset,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $sql = "
            SELECT
                id,
                name,
                is_active,
                created_at,
                updated_at
            FROM rooms
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'id' => $id,
        ]);

        $room = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($room === false) {
            return null;
        }

        return $room;
    }

    public function create(string $name): int
    {
        $sql = '
            INSERT INTO rooms (name)
            VALUES (:name)
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'name' => $name,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(
        int $id,
        string $name,
        bool $isActive = true
    ): bool {
        $sql = '
            UPDATE rooms
            SET
                name = :name,
                is_active = :is_active
            WHERE id = :id
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'name' => $name,
            'is_active' => $isActive ? 1 : 0,
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function deactivate(int $id): bool
    {
        $sql = '
            UPDATE rooms
            SET is_active = 0
            WHERE id = :id
              AND is_active = 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }
}
