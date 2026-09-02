<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use Tests\Support\FeatureRecordingOrderRepository;
use Tests\Support\OrderFeatureTestCase;

final class ClientTamperingTest extends OrderFeatureTestCase
{
    public function test_server_overrides_client_side_price_tampering(): void
    {
        $orders = new FeatureRecordingOrderRepository();
        $service = $this->makeService(orders: $orders);

        $service->place(
            $this->demoUser(),
            $this->placeRequestFromFixture('tampered_price_payload'),
        );

        self::assertSame('10.00', $orders->lastTotal());
        self::assertSame('10.00', $orders->lastItems()[0]['unit_price_snapshot']);
        self::assertSame('10.00', $orders->lastItems()[0]['line_total']);
    }
}
