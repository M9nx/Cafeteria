<?php

declare(strict_types=1);

namespace Cafeteria\Repositories\Pdo;

use Cafeteria\Repositories\Contracts\CategoryRepositoryInterface;
use PDO;

final class PdoCategoryRepository implements CategoryRepositoryInterface
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
            FROM categories
            WHERE is_active = 1
            ORDER BY name ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            "SELECT COUNT(*) FROM categories"
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
            FROM categories
            ORDER BY name ASC
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
            FROM categories
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'id' => $id,
        ]);

        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($category === false) {
            return null;
        }

        return $category;
    }

    public function create(string $name): int
    {
        $sql = "
            INSERT INTO categories (name)
            VALUES (:name)
        ";

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
        $sql = "
            UPDATE categories
            SET
                name = :name,
                is_active = :is_active
            WHERE id = :id
        ";

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
        $sql = "
            UPDATE categories
            SET is_active = 0
            WHERE id = :id
              AND is_active = 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }
}