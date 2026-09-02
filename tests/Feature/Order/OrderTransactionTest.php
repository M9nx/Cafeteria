<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use RuntimeException;
use Tests\Support\FeatureFailingOrderRepository;
use Tests\Support\OrderFeatureTestCase;

final class OrderTransactionTest extends OrderFeatureTestCase
{
    public function test_order_creation_rolls_back_entirely_on_failure(): void
    {
        $pdo = $this->sqliteWithRoom();
        $orders = new FeatureFailingOrderRepository();
        $service = $this->makeService(orders: $orders);

        try {
            $service->place(
                $this->demoUser(),
                $this->placeRequestFromFixture('valid_cart'),
            );

            self::fail('Expected order placement to fail.');
        } catch (RuntimeException $exception) {
            self::assertSame(
                'Simulated item insert failure',
                $exception->getMessage(),
            );
        }

        self::assertFalse($pdo->inTransaction());
        self::assertSame([], $orders->lastItems());
    }
}
