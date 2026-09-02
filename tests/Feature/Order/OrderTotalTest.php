<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use Tests\Support\FeatureRecordingOrderRepository;
use Tests\Support\OrderFeatureTestCase;

final class OrderTotalTest extends OrderFeatureTestCase
{
    public function test_server_calculates_correct_totals_using_decimal_rules(): void
    {
        $orders = new FeatureRecordingOrderRepository();
        $service = $this->makeService(orders: $orders);

        $service->place(
            $this->demoUser(),
            $this->placeRequestFromFixture('valid_cart'),
        );

        self::assertSame('35.00', $orders->lastTotal());
        self::assertSame('20.00', $orders->lastItems()[0]['line_total']);
        self::assertSame('15.00', $orders->lastItems()[1]['line_total']);
    }

    public function test_server_ignores_posted_total_field(): void
    {
        $orders = new FeatureRecordingOrderRepository();
        $service = $this->makeService(orders: $orders);

        $service->place(
            $this->demoUser(),
            $this->placeRequestFromFixture('tampered_total_cart'),
        );

        self::assertSame('10.00', $orders->lastTotal());
        self::assertNotSame('0.01', $orders->lastTotal());
    }
}
