<?php

declare(strict_types=1);

namespace Cafeteria\Repositories\Pdo;

use Cafeteria\Repositories\Contracts\OrderCommandRepositoryInterface;
use DateTimeImmutable;
use PDO;
use PDOException;
use RuntimeException;

final class PdoOrderCommandRepository implements OrderCommandRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {
    }

    public function insertOrder(
        int $userId,
        int $createdByUserId,
        int $roomId,
        ?string $notes,
        string $totalAmount,
    ): int {
        $statement = $this->pdo->prepare(
            'INSERT INTO orders
                (user_id, created_by_user_id, room_id, status, notes, total_amount)
             VALUES
                (:user_id, :created_by_user_id, :room_id, :status, :notes, :total_amount)'
        );

        $statement->execute([
            'user_id' => $userId,
            'created_by_user_id' => $createdByUserId,
            'room_id' => $roomId,
            'status' => 'PROCESSING',
            'notes' => $notes,
            'total_amount' => $totalAmount,
        ]);

        $orderId = (int) $this->pdo->lastInsertId();

        if ($orderId < 1) {
            throw new RuntimeException('Unable to create order.');
        }

        return $orderId;
    }

    public function insertItems(int $orderId, array $items): void
    {
        if ($items === []) {
            throw new RuntimeException('Order items are required.');
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO order_items
                (order_id, product_id, product_name_snapshot,
                 unit_price_snapshot, quantity, line_total)
             VALUES
                (:order_id, :product_id, :product_name_snapshot,
                 :unit_price_snapshot, :quantity, :line_total)'
        );

        foreach ($items as $item) {
            $statement->execute([
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'product_name_snapshot' => $item['product_name_snapshot'],
                'unit_price_snapshot' => $item['unit_price_snapshot'],
                'quantity' => $item['quantity'],
                'line_total' => $item['line_total'],
            ]);
        }
    }

    public function cancelIfProcessing(
        int $orderId,
        int $changedByUserId,
        DateTimeImmutable $cancelledAt,
    ): bool {
        $statement = $this->pdo->prepare(
            'UPDATE orders
             SET status = :status,
                 cancelled_at = :cancelled_at
             WHERE id = :order_id
               AND status = :processing'
        );

        $statement->execute([
            'status' => 'CANCELLED',
            'cancelled_at' => $cancelledAt->format('Y-m-d H:i:s'),
            'order_id' => $orderId,
            'processing' => 'PROCESSING',
        ]);

        if ($statement->rowCount() !== 1) {
            return false;
        }

        $this->recordStatusChange(
            $orderId,
            'PROCESSING',
            'CANCELLED',
            $changedByUserId,
            $cancelledAt,
        );

        return true;
    }

    public function transitionIfCurrent(
        int $orderId,
        string $fromStatus,
        string $toStatus,
        int $changedByUserId,
        DateTimeImmutable $changedAt,
    ): bool {
        $statement = $this->pdo->prepare(
            'UPDATE orders
             SET status = :to_status
             WHERE id = :order_id
               AND status = :from_status'
        );

        $statement->execute([
            'to_status' => $toStatus,
            'order_id' => $orderId,
            'from_status' => $fromStatus,
        ]);

        if ($statement->rowCount() !== 1) {
            return false;
        }

        $this->recordStatusChange(
            $orderId,
            $fromStatus,
            $toStatus,
            $changedByUserId,
            $changedAt,
        );

        return true;
    }

    private function recordStatusChange(
        int $orderId,
        string $fromStatus,
        string $toStatus,
        int $changedByUserId,
        DateTimeImmutable $changedAt,
    ): void {
        $statement = $this->pdo->prepare(
            'INSERT INTO order_status_history
                (order_id, from_status, to_status, changed_by_user_id, changed_at)
             VALUES
                (:order_id, :from_status, :to_status, :changed_by_user_id, :changed_at)'
        );

        $statement->execute([
            'order_id' => $orderId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by_user_id' => $changedByUserId,
            'changed_at' => $changedAt->format('Y-m-d H:i:s'),
        ]);
    }
}
