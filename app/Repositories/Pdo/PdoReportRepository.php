<?php

declare(strict_types=1);

namespace Cafeteria\Repositories\Pdo;

use Cafeteria\Repositories\Contracts\ReportRepositoryInterface;
use PDO;

final class PdoReportRepository implements ReportRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {
    }

    /**
     * @param array{
     *     from?: string,
     *     to?: string,
     *     user_id?: int,
     *     include_cancelled?: bool
     * } $filter
     *
     * @return array<string, mixed>
     */
    public function summarize(array $filter): array
    {
        $where = ['1 = 1'];
        $params = [];

        $this->applyFilters($where, $params, $filter);

        $whereSql = implode(' AND ', $where);

        $statement = $this->pdo->prepare(
            'SELECT
                o.user_id,
                u.name AS user_name,
                COUNT(*) AS order_count,
                COALESCE(SUM(o.total_amount), 0) AS total_amount
             FROM orders o
             INNER JOIN users u
                 ON u.id = o.user_id
             WHERE ' . $whereSql . '
             GROUP BY o.user_id, u.name
             ORDER BY total_amount DESC, o.user_id ASC'
        );

        $statement->execute($params);

        return [
            'users' => $statement->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    /**
     * @param array{
     *     from?: string,
     *     to?: string,
     *     include_cancelled?: bool
     * } $filter
     *
     * @return array<int, array<string, mixed>>
     */
    public function ordersForUser(
        int $userId,
        array $filter
    ): array {
        $where = ['o.user_id = :user_id'];
        $params = [
            'user_id' => $userId,
        ];

        $this->applyFilters($where, $params, $filter);

        $whereSql = implode(' AND ', $where);

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
                o.updated_at,
                o.cancelled_at
             FROM orders o
             INNER JOIN users u
                 ON u.id = o.user_id
             INNER JOIN rooms r
                 ON r.id = o.room_id
             WHERE ' . $whereSql . '
             ORDER BY o.created_at DESC, o.id DESC'
        );

        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array{
     *     from?: string,
     *     to?: string,
     *     include_cancelled?: bool
     * } $filter
     *
     * @return array<string, mixed>|null
     */
    public function orderDetailsForReport(
        int $orderId,
        array $filter
    ): ?array {
        $where = [
            'o.id = :order_id',
        ];

        $params = [
            'order_id' => $orderId,
        ];

        $this->applyFilters($where, $params, $filter);

        $whereSql = implode(' AND ', $where);

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
             WHERE ' . $whereSql . '
             LIMIT 1'
        );

        $statement->execute($params);

        $order = $statement->fetch(PDO::FETCH_ASSOC);

        if ($order === false) {
            return null;
        }

        $itemsStatement = $this->pdo->prepare(
            'SELECT
                id,
                order_id,
                product_id,
                product_name_snapshot,
                unit_price_snapshot,
                quantity,
                line_total
             FROM order_items
             WHERE order_id = :order_id
             ORDER BY id ASC'
        );

        $itemsStatement->execute([
            'order_id' => $orderId,
        ]);

        $order['items'] = $itemsStatement->fetchAll(PDO::FETCH_ASSOC);

        return $order;
    }

    /**
     * @return array{id: int|string, name: string, email: string}|null
     */
    public function findReportUser(int $userId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, name, email
             FROM users
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute([
            'id' => $userId,
        ]);

        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return $user === false ? null : $user;
    }

    /**
     * @param array<int, string> $where
     * @param array<string, mixed> $params
     * @param array<string, mixed> $filter
     */
    private function applyFilters(
        array &$where,
        array &$params,
        array $filter
    ): void {
        if (
            isset($filter['from'])
            && is_string($filter['from'])
            && trim($filter['from']) !== ''
        ) {
            $where[] = 'o.created_at >= :from';
            $params['from'] = trim($filter['from']);
        }

        if (
            isset($filter['to'])
            && is_string($filter['to'])
            && trim($filter['to']) !== ''
        ) {
            $where[] = 'o.created_at <= :to';
            $params['to'] = trim($filter['to']) . ' 23:59:59';
        }

        if (
            isset($filter['user_id'])
            && is_int($filter['user_id'])
        ) {
            $where[] = 'o.user_id = :filter_user_id';
            $params['filter_user_id'] = $filter['user_id'];
        }

        if (($filter['include_cancelled'] ?? false) !== true) {
            $where[] = 'o.status <> :cancelled_status';
            $params['cancelled_status'] = 'CANCELLED';
        }
    }
}
