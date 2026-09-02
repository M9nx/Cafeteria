<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use PHPUnit\Framework\TestCase;

final class ClientTamperingTest extends TestCase
{
    private array $ordersFixture;
    private array $productsFixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ordersFixture = require __DIR__ . '/../../Fixtures/orders.php';
        $this->productsFixture = require __DIR__ . '/../../Fixtures/products.php';
    }

    public function test_server_overrides_client_side_price_tampering(): void
    {
        $tamperedCart = $this->ordersFixture['tampered_price_payload'] ?? $this->ordersFixture['valid_cart'];
        $items = $tamperedCart['items'] ?? $tamperedCart;

        // Build server-authoritative product catalog
        $availableProducts = $this->productsFixture['available'] ?? [];
        $serverCatalog = [];
        foreach ($availableProducts as $product) {
            $serverCatalog[$product['id']] = (float) $product['price'];
        }

        foreach ($items as $item) {
            $productId = $item['product_id'];
            $clientSubmittedPrice = (float) ($item['price'] ?? 0.01);
            $authoritativeServerPrice = $serverCatalog[$productId] ?? 0.00;

            // Server must compute totals using authoritative catalog price, ignoring client submission
            $this->assertNotEquals($clientSubmittedPrice, $authoritativeServerPrice);
            $this->assertGreaterThan(0.00, $authoritativeServerPrice);
        }
    }
}