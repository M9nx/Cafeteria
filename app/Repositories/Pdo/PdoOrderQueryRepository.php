<?php

declare(strict_types=1);

namespace Cafeteria\Repositories\Pdo;

use Cafeteria\Repositories\Contracts\OrderQueryRepositoryInterface;
use DateTimeImmutable;
use PDO;

final class PdoOrderQueryRepository implements OrderQueryRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {
    }

    public function findLatestForUser(int $userId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                o.id,
                o.user_id,
                o.room_id,
                r.name AS room_name,
                o.status,
                o.notes,
                o.total_amount,
                o.created_at,
                o.updated_at,
                o.cancelled_at
             FROM orders o
             INNER JOIN rooms r
                 ON r.id = o.room_id
             WHERE o.user_id = :user_id
             ORDER BY o.created_at DESC, o.id DESC
             LIMIT 1'
        );

        $statement->execute([
            'user_id' => $userId,
        ]);

        $order = $statement->fetch(PDO::FETCH_ASSOC);

        return $order === false ? null : $order;
    }

    public function paginateForUser(
        int $userId,
        ?DateTimeImmutable $from,
        ?DateTimeImmutable $to,
        int $page = 1,
        int $perPage = 15
    ): array {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        $where = ['o.user_id = :user_id'];
        $params = [
            'user_id' => $userId,
        ];

        if ($from !== null) {
            $where[] = 'o.created_at >= :from';
            $params['from'] = $from->format('Y-m-d H:i:s');
        }

        if ($to !== null) {
            $where[] = 'o.created_at <= :to';
            $params['to'] = $to->format('Y-m-d H:i:s');
        }

        $whereSql = implode(' AND ', $where);

        $countStatement = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM orders o
             WHERE ' . $whereSql
        );

        $countStatement->execute($params);

        $total = (int) $countStatement->fetchColumn();

        $offset = ($page - 1) * $perPage;

        $statement = $this->pdo->prepare(
            'SELECT
                o.id,
                o.user_id,
                o.room_id,
                r.name AS room_name,
                o.status,
                o.notes,
                o.total_amount,
                o.created_at,
                o.updated_at,
                o.cancelled_at
             FROM orders o
             INNER JOIN rooms r
                 ON r.id = o.room_id
             WHERE ' . $whereSql . '
             ORDER BY o.created_at DESC, o.id DESC
             LIMIT :limit OFFSET :offset'
        );

        foreach ($params as $name => $value) {
            $statement->bindValue(
                ':' . $name,
                $value,
                PDO::PARAM_STR
            );
        }

        $statement->bindValue(
            ':limit',
            $perPage,
            PDO::PARAM_INT
        );

        $statement->bindValue(
            ':offset',
            $offset,
            PDO::PARAM_INT
        );

        $statement->execute();

        return [
            'items' => $statement->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    public function findOwnedDetail(
        int $orderId,
        int $userId
    ): ?array {
        return $this->findDetail(
            $orderId,
            'o.user_id = :user_id',
            ['user_id' => $userId]
        );
    }

    public function findDetailForAdmin(int $orderId): ?array
    {
        return $this->findDetail(
            $orderId,
            '1 = 1',
            []
        );
    }

    public function listCurrentQueue(
        int $page = 1,
        int $perPage = 15
    ): array {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        $where = "
            o.status IN ('PROCESSING', 'OUT_FOR_DELIVERY')
        ";

        $countStatement = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM orders o
             WHERE ' . $where
        );

        $countStatement->execute();

        $total = (int) $countStatement->fetchColumn();

        $offset = ($page - 1) * $perPage;

        $statement = $this->pdo->prepare(
            'SELECT
                o.id,
                o.user_id,
                u.name AS user_name,
                o.room_id,
                r.name AS room_name,
                o.status,
                o.notes,
                o.total_amount,
                o.created_at,
                o.updated_at
             FROM orders o
             INNER JOIN users u
                 ON u.id = o.user_id
             INNER JOIN rooms r
                 ON r.id = o.room_id
             WHERE ' . $where . '
             ORDER BY o.created_at ASC, o.id ASC
             LIMIT :limit OFFSET :offset'
        );

        $statement->bindValue(
            ':limit',
            $perPage,
            PDO::PARAM_INT
        );

        $statement->bindValue(
            ':offset',
            $offset,
            PDO::PARAM_INT
        );

        $statement->execute();

        return [
            'items' => $statement->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    /**
     * @param array<string, mixed> $params
     */
    private function findDetail(
        int $orderId,
        string $scope,
        array $params
    ): ?array {
        $statement = $this->pdo->prepare(
            'SELECT
                o.id,
                o.user_id,
                u.name AS user_name,
                u.email AS user_email,
                o.room_id,
                r.name AS room_name,
                o.status,
                o.notes,
                o.total_amount,
                o.created_at,
                o.updated_at,
                o.cancelled_at
             FROM orders o
             INNER JOIN users u
                 ON u.id = o.user_id
             INNER JOIN rooms r
                 ON r.id = o.room_id
             WHERE o.id = :order_id
               AND ' . $scope . '
             LIMIT 1'
        );

        $statement->execute(
            array_merge(
                ['order_id' => $orderId],
                $params
            )
        );

        $order = $statement->fetch(PDO::FETCH_ASSOC);

        if ($order === false) {
            return null;
        }

        $itemsStatement = $this->pdo->prepare(
            'SELECT
                oi.id,
                oi.order_id,
                oi.product_id,
                oi.product_name_snapshot,
                oi.unit_price_snapshot,
                oi.quantity,
                oi.line_total,
                p.image_path AS product_image_path
             FROM order_items oi
             LEFT JOIN products p
                 ON p.id = oi.product_id
             WHERE oi.order_id = :order_id
             ORDER BY oi.id ASC'
        );

        $itemsStatement->execute([
            'order_id' => $orderId,
        ]);

        $order['items'] = $itemsStatement->fetchAll(
            PDO::FETCH_ASSOC
        );

        return $order;
    }
}