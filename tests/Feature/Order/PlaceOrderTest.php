<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use InvalidArgumentException;
use Tests\Support\FeatureRecordingOrderRepository;
use Tests\Support\OrderFeatureTestCase;

final class PlaceOrderTest extends OrderFeatureTestCase
{
    public function test_can_place_order_with_valid_cart(): void
    {
        $orders = new FeatureRecordingOrderRepository();
        $service = $this->makeService(orders: $orders);

        $orderId = $service->place(
            $this->demoUser(),
            $this->placeRequestFromFixture('valid_cart'),
        );

        self::assertSame(101, $orderId);
        self::assertSame('35.00', $orders->lastTotal());
        self::assertCount(2, $orders->lastItems());
    }

    public function test_cannot_place_order_with_empty_cart(): void
    {
        $service = $this->makeService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('at least one item');

        $service->place(
            $this->demoUser(),
            $this->placeRequestFromFixture('empty_cart'),
        );
    }

    public function test_cannot_place_order_with_invalid_quantity(): void
    {
        $service = $this->makeService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be at least 1.');

        $service->place(
            $this->demoUser(),
            $this->placeRequestFromFixture('invalid_quantity'),
        );
    }

    public function test_cannot_place_order_with_negative_quantity(): void
    {
        $service = $this->makeService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be at least 1.');

        $service->place(
            $this->demoUser(),
            $this->placeRequestFromFixture('negative_quantity'),
        );
    }

    public function test_cannot_place_order_with_unavailable_product(): void
    {
        $service = $this->makeService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unavailable');

        $service->place(
            $this->demoUser(),
            $this->placeRequestFromFixture('unavailable_product'),
        );
    }
}
