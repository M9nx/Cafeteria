<?php

declare(strict_types=1);

namespace Tests\Integration\Orders;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Database\ConnectionFactory;
use Cafeteria\Domain\Users\Role;
use Cafeteria\DTO\OrderItemInput;
use Cafeteria\DTO\PlaceOrderRequest;
use Cafeteria\Repositories\Contracts\OrderCommandRepositoryInterface;
use Cafeteria\Repositories\Pdo\PdoOrderCommandRepository;
use Cafeteria\Services\OrderService;
use Cafeteria\Validation\PlaceOrderValidator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Unit\Services\FakeProductRepository;

final class OrderTransactionTest extends TestCase
{
    public function test_failed_item_insert_leaves_no_persisted_order(): void
    {
        $config = require dirname(__DIR__, 3) . '/config/database.php';
        $pdo = (new ConnectionFactory())->make($config);

        try {
            $pdo->query('SELECT 1');
        } catch (\Throwable) {
            self::markTestSkipped('Database is not reachable.');
        }

        $before = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();

        $productId = (int) $pdo->query(
            'SELECT id FROM products
             WHERE is_available = 1 AND deleted_at IS NULL
             ORDER BY id ASC LIMIT 1'
        )->fetchColumn();

        if ($productId < 1) {
            self::markTestSkipped('No seeded products available.');
        }

        $roomId = (int) $pdo->query(
            'SELECT id FROM rooms ORDER BY id ASC LIMIT 1'
        )->fetchColumn();

        $product = $pdo->query(
            "SELECT name, price FROM products WHERE id = {$productId}"
        )->fetch(\PDO::FETCH_ASSOC);

        $service = new OrderService(
            new FakeProductRepository([
                $productId => [
                    'name' => (string) $product['name'],
                    'price' => (string) $product['price'],
                ],
            ]),
            new FailingPdoOrderCommandRepository($pdo),
            new PlaceOrderValidator(),
            $pdo,
        );

        $user = new AuthenticatedUser(
            2,
            'user@example.test',
            'Demo User',
            Role::User,
        );

        try {
            $service->place(
                $user,
                new PlaceOrderRequest(
                    roomId: $roomId,
                    notes: null,
                    items: [new OrderItemInput($productId, 1)],
                ),
            );

            self::fail('Expected transactional placement to fail.');
        } catch (RuntimeException) {
            // expected simulated failure
        }

        $after = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();

        self::assertSame($before, $after);
    }
}

final class FailingPdoOrderCommandRepository extends PdoOrderCommandRepository
{
    public function insertItems(int $orderId, array $items): void
    {
        throw new RuntimeException('Simulated item insert failure');
    }
}
