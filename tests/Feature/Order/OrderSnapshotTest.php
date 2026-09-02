<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use Tests\Support\FeatureFakeProductRepository;
use Tests\Support\FeatureRecordingOrderRepository;
use Tests\Support\OrderFeatureTestCase;

final class OrderSnapshotTest extends OrderFeatureTestCase
{
    public function test_order_item_snapshot_persists_historical_values_when_product_price_changes(): void
    {
        $products = new FeatureFakeProductRepository($this->availableCatalogProducts());
        $orders = new FeatureRecordingOrderRepository();
        $service = $this->makeService($products, $orders);

        $service->place(
            $this->demoUser(),
            $this->placeRequestFromFixture('valid_cart'),
        );

        $firstOrderItems = $orders->lastItems();

        $products->updateProduct(1, ['price' => '25.00']);

        self::assertSame('10.00', $firstOrderItems[0]['unit_price_snapshot']);
        self::assertSame('20.00', $firstOrderItems[0]['line_total']);
    }

    public function test_order_item_snapshot_retains_name_if_product_renamed(): void
    {
        $products = new FeatureFakeProductRepository($this->availableCatalogProducts());
        $orders = new FeatureRecordingOrderRepository();
        $service = $this->makeService($products, $orders);

        $service->place(
            $this->demoUser(),
            $this->placeRequestFromFixture('valid_cart'),
        );

        $firstOrderItems = $orders->lastItems();

        $products->updateProduct(1, ['name' => 'Green Tea Premium']);

        self::assertSame('Tea', $firstOrderItems[0]['product_name_snapshot']);
    }
}
