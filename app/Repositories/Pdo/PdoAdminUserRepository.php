<?php

declare(strict_types=1);

namespace Cafeteria\Repositories\Pdo;

use Cafeteria\Repositories\Contracts\AdminUserRepositoryInterface;
use PDO;

final class PdoAdminUserRepository implements AdminUserRepositoryInterface
{
    public function __construct(
        private PDO $pdo
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

        $countSql = '
            SELECT COUNT(*)
            FROM users
        ';

        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute();

        $total = (int) $countStmt->fetchColumn();

        /*
         * Calculate pagination offset.
         */
        $offset = ($page - 1) * $perPage;

        $sql = '
            SELECT
                id,
                name,
                email,
                role,
                room_id,
                extension,
                profile_image_path,
                is_active,
                created_at,
                updated_at
            FROM users
            ORDER BY id DESC
            LIMIT :limit OFFSET :offset
        ';

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
                email,
                role,
                room_id,
                extension,
                profile_image_path,
                is_active,
                created_at,
                updated_at
            FROM users
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'id' => $id,
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user === false) {
            return null;
        }

        return $user;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): int
    {
        $sql = "
            INSERT INTO users (
                name,
                email,
                password_hash,
                role,
                room_id,
                extension,
                profile_image_path
            )
            VALUES (
                :name,
                :email,
                :password_hash,
                :role,
                :room_id,
                :extension,
                :profile_image_path
            )
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'password_hash' => $attributes['password_hash'],
            'role' => $this->normalizedRole($attributes['role'] ?? null),
            'room_id' => $attributes['room_id'] ?? null,
            'extension' => $attributes['extension'] ?? null,
            'profile_image_path' => $attributes['profile_image_path'] ?? null,
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

        if (array_key_exists('name', $attributes)) {
            $fields[] = 'name = :name';
            $params['name'] = $attributes['name'];
        }

        if (array_key_exists('email', $attributes)) {
            $fields[] = 'email = :email';
            $params['email'] = $attributes['email'];
        }

        if (array_key_exists('password_hash', $attributes)) {
            $fields[] = 'password_hash = :password_hash';
            $params['password_hash'] = $attributes['password_hash'];
        }

        if (array_key_exists('role', $attributes)) {
            $fields[] = 'role = :role';
            $params['role'] = $this->normalizedRole($attributes['role']);
        }

        if (array_key_exists('room_id', $attributes)) {
            $fields[] = 'room_id = :room_id';
            $params['room_id'] = $attributes['room_id'];
        }

        if (array_key_exists('extension', $attributes)) {
            $fields[] = 'extension = :extension';
            $params['extension'] = $attributes['extension'];
        }

        if (array_key_exists('profile_image_path', $attributes)) {
            $fields[] = 'profile_image_path = :profile_image_path';
            $params['profile_image_path'] =
                $attributes['profile_image_path'];
        }

        if (array_key_exists('is_active', $attributes)) {
            $fields[] = 'is_active = :is_active';
            $params['is_active'] =
                $attributes['is_active'] ? 1 : 0;
        }

        if ($fields === []) {
            return false;
        }

        $sql = "
            UPDATE users
            SET " . implode(', ', $fields) . "
            WHERE id = :id
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    public function deactivate(int $id): bool
    {
        $sql = "
            UPDATE users
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

    public function countActiveAdmins(): int
    {
        $statement = $this->pdo->query(
            "SELECT COUNT(*)
             FROM users
             WHERE role = 'ADMIN'
               AND is_active = 1"
        );

        if ($statement === false) {
            return 0;
        }

        return (int) $statement->fetchColumn();
    }

    private function normalizedRole(mixed $role): string
    {
        $normalized = strtoupper(trim((string) $role));

        return $normalized === 'ADMIN' ? 'ADMIN' : 'USER';
    }
}
