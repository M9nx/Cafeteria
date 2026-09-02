<?php

declare(strict_types=1);

namespace Cafeteria\Repositories\Pdo;

use Cafeteria\Repositories\Contracts\ProductRepositoryInterface;
use PDO;

final class PdoProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {
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
            'SELECT COUNT(*)
             FROM products
             WHERE deleted_at IS NULL'
        );
        $countStmt->execute();

        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare(
            'SELECT
                p.id,
                p.category_id,
                c.name AS category_name,
                p.name,
                p.price,
                p.image_path,
                p.is_available,
                p.created_at,
                p.updated_at,
                p.deleted_at
             FROM products p
             INNER JOIN categories c
                 ON c.id = p.category_id
             WHERE p.deleted_at IS NULL
             ORDER BY p.name ASC, p.id ASC
             LIMIT :limit OFFSET :offset'
        );

        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    public function paginateAvailable(
        int $page = 1,
        int $perPage = 15
    ): array {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        $countStmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM products
             WHERE is_available = 1
               AND deleted_at IS NULL'
        );
        $countStmt->execute();

        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare(
            'SELECT
                p.id,
                p.category_id,
                c.name AS category_name,
                p.name,
                p.price,
                p.image_path,
                p.is_available
             FROM products p
             INNER JOIN categories c
                 ON c.id = p.category_id
             WHERE p.is_available = 1
               AND p.deleted_at IS NULL
             ORDER BY p.name ASC, p.id ASC
             LIMIT :limit OFFSET :offset'
        );

        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
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
        $stmt = $this->pdo->prepare(
            'SELECT
                id,
                category_id,
                name,
                price,
                image_path,
                is_available,
                created_at,
                updated_at,
                deleted_at
             FROM products
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        return $product === false ? null : $product;
    }

    /**
     * @param list<int> $ids
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAvailableByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $ids = array_values(
            array_unique(
                array_map('intval', $ids)
            )
        );

        $placeholders = implode(
            ', ',
            array_fill(0, count($ids), '?')
        );

        $stmt = $this->pdo->prepare(
            'SELECT
                id,
                name,
                price,
                image_path
             FROM products
             WHERE id IN (' . $placeholders . ')
               AND is_available = 1
               AND deleted_at IS NULL'
        );

        $stmt->execute($ids);

        $products = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $product) {
            $products[(int) $product['id']] = $product;
        }

        return $products;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO products (
                category_id,
                name,
                price,
                image_path,
                is_available
             )
             VALUES (
                :category_id,
                :name,
                :price,
                :image_path,
                :is_available
             )'
        );

        $stmt->execute([
            'category_id' => $attributes['category_id'],
            'name' => $attributes['name'],
            'price' => $attributes['price'],
            'image_path' => $attributes['image_path'] ?? null,
            'is_available' => !empty($attributes['is_available']) ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(
        int $id,
        array $attributes
    ): bool {
        $fields = [];
        $params = [
            'id' => $id,
        ];

        if (array_key_exists('category_id', $attributes)) {
            $fields[] = 'category_id = :category_id';
            $params['category_id'] = $attributes['category_id'];
        }

        if (array_key_exists('name', $attributes)) {
            $fields[] = 'name = :name';
            $params['name'] = $attributes['name'];
        }

        if (array_key_exists('price', $attributes)) {
            $fields[] = 'price = :price';
            $params['price'] = $attributes['price'];
        }

        if (array_key_exists('image_path', $attributes)) {
            $fields[] = 'image_path = :image_path';
            $params['image_path'] = $attributes['image_path'];
        }

        if (array_key_exists('is_available', $attributes)) {
            $fields[] = 'is_available = :is_available';
            $params['is_available'] =
                $attributes['is_available'] ? 1 : 0;
        }

        if ($fields === []) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE products
             SET ' . implode(', ', $fields) . '
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    public function softDelete(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE products
             SET
                is_available = 0,
                deleted_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }
}