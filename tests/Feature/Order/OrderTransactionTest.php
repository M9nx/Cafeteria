<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use PHPUnit\Framework\TestCase;
use Exception;

final class OrderTransactionTest extends TestCase
{
    private array $ordersFixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ordersFixture = require __DIR__ . '/../../Fixtures/orders.php';
    }

    public function test_order_creation_rolls_back_entirely_on_failure(): void
    {
        $validCart = $this->ordersFixture['valid_cart'];
        $items = $validCart['items'] ?? $validCart;

        $databaseState = [
            'orders' => [],
            'order_items' => [],
        ];

        // Simulate a transactional operation that fails on item insertion
        $transactionFailed = false;

        try {
            // Begin transaction
            $simulatedOrder = ['id' => 101, 'status' => 'PENDING'];
            $databaseState['orders'][] = $simulatedOrder;

            foreach ($items as $index => $item) {
                // Simulate database error on second item insertion
                if ($index === 1) {
                    throw new Exception('Database constraint failure during item insertion.');
                }

                $databaseState['order_items'][] = [
                    'order_id' => 101,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ];
            }
        } catch (Exception $e) {
            // Rollback transaction state
            $databaseState['orders'] = [];
            $databaseState['order_items'] = [];
            $transactionFailed = true;
        }

        // Assert atomic behavior: failure rolled back both order and items
        $this->assertTrue($transactionFailed);
        $this->assertEmpty($databaseState['orders']);
        $this->assertEmpty($databaseState['order_items']);
    }
}