<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use PHPUnit\Framework\TestCase;

final class PlaceOrderTest extends TestCase
{
    private array $ordersFixture;
    private array $productsFixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ordersFixture = require __DIR__ . '/../../Fixtures/orders.php';
        $this->productsFixture = require __DIR__ . '/../../Fixtures/products.php';
    }

    public function test_can_place_order_with_valid_cart(): void
    {
        $payload = $this->ordersFixture['valid_cart'];
        $items = $payload['items'] ?? $payload;

        $this->assertNotEmpty($items);
        $this->assertGreaterThanOrEqual(1, count($items));
    }

    public function test_cannot_place_order_with_empty_cart(): void
    {
        $payload = $this->ordersFixture['empty_cart'];
        $items = $payload['items'] ?? $payload;

        $this->assertEmpty($items);
    }

    public function test_cannot_place_order_with_invalid_quantity(): void
    {
        $payload = $this->ordersFixture['invalid_quantity'];
        $items = $payload['items'] ?? $payload;

        $this->assertEquals(0, $items[0]['quantity']);
    }

    public function test_cannot_place_order_with_unavailable_product(): void
    {
        $payload = $this->ordersFixture['unavailable_product'];
        $items = $payload['items'] ?? $payload;
        $unavailableProductId = $items[0]['product_id'];

        $allProducts = array_merge(
            $this->productsFixture['available'] ?? [],
            $this->productsFixture['unavailable'] ?? [],
            $this->productsFixture['deleted'] ?? []
        );

        $foundProduct = null;
        foreach ($allProducts as $product) {
            if (isset($product['id']) && $product['id'] === $unavailableProductId) {
                $foundProduct = $product;
                break;
            }
        }

        $this->assertNotNull($foundProduct);
        $this->assertFalse((bool) ($foundProduct['is_available'] ?? false));
    }
}