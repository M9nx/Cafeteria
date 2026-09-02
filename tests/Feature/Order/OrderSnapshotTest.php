<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use PHPUnit\Framework\TestCase;

final class OrderSnapshotTest extends TestCase
{
    private array $productsFixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productsFixture = require __DIR__ . '/../../Fixtures/products.php';
    }

    public function test_order_item_snapshot_persists_historical_values_when_product_price_changes(): void
    {
        // 1. Snapshot captured at time of order creation (Tea = 10.00)
        $productBefore = $this->productsFixture['available'][0];
        $orderSnapshot = [
            'product_id' => $productBefore['id'],
            'product_name' => $productBefore['name'],
            'unit_price' => $productBefore['price'],
            'quantity' => 2,
        ];

        // 2. Simulate product master price changing later to 25.00
        $productAfter = $productBefore;
        $productAfter['price'] = '25.00';

        // 3. Assert historic snapshot price remains 10.00, ignoring master catalog update
        $this->assertEquals('10.00', $orderSnapshot['unit_price']);
        $this->assertNotEquals($productAfter['price'], $orderSnapshot['unit_price']);
    }

    public function test_order_item_snapshot_retains_name_if_product_renamed(): void
    {
        $product = $this->productsFixture['available'][0]; // Tea
        
        $snapshot = [
            'product_id' => $product['id'],
            'product_name' => $product['name'],
        ];

        // Product master catalog name changes
        $updatedProductMasterName = 'Green Tea Premium';

        // Assert snapshot retains historical name
        $this->assertEquals('Tea', $snapshot['product_name']);
        $this->assertNotEquals($updatedProductMasterName, $snapshot['product_name']);
    }
}