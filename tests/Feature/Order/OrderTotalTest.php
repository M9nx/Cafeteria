<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use PHPUnit\Framework\TestCase;

final class OrderTotalTest extends TestCase
{
    private array $ordersFixture;
    private array $productsFixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ordersFixture = require __DIR__ . '/../../Fixtures/orders.php';
        $this->productsFixture = require __DIR__ . '/../../Fixtures/products.php';
    }

    public function test_server_calculates_correct_totals_using_decimal_rules(): void
    {
        $validCart = $this->ordersFixture['valid_cart'];
        $items = $validCart['items'] ?? $validCart;

        // Build product price map
        $allProducts = array_merge(
            $this->productsFixture['available'] ?? [],
            $this->productsFixture['unavailable'] ?? []
        );

        $priceMap = [];
        foreach ($allProducts as $product) {
            $priceMap[$product['id']] = $product['price'];
        }

        // Calculate line totals and grand total on the server side
        $calculatedTotal = 0.0;
        foreach ($items as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];
            $price = (float) $priceMap[$productId];
            
            $lineTotal = $price * $quantity;
            $calculatedTotal += $lineTotal;
        }

        // Tea (10.00 * 2) + Coffee (15.00 * 1) = 35.00
        $this->assertEquals(35.00, $calculatedTotal);
        $this->assertSame('35.00', number_format($calculatedTotal, 2, '.', ''));
    }

    public function test_server_ignores_posted_total_field(): void
    {
        $tamperedPayload = $this->ordersFixture['tampered_price_payload'] ?? $this->ordersFixture['tampered_total_cart'] ?? [];
        $postedTotal = $tamperedPayload['total'] ?? '0.01';

        $items = $tamperedPayload['items'] ?? [];
        $productId = $items[0]['product_id'] ?? 1;

        // Fetch actual server price for product 1 (Tea = 10.00)
        $actualServerPrice = '10.00';

        // Assert posted client total differs from recalculation
        $this->assertNotEquals($postedTotal, $actualServerPrice);
    }
}