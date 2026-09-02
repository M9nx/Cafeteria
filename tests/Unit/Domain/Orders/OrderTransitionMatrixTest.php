<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Orders;

use Cafeteria\Domain\Orders\OrderTransitionMatrix;
use PHPUnit\Framework\TestCase;

final class OrderTransitionMatrixTest extends TestCase
{
    public function test_allows_processing_to_out_for_delivery(): void
    {
        self::assertTrue(
            OrderTransitionMatrix::canTransition('PROCESSING', 'OUT_FOR_DELIVERY'),
        );
    }

    public function test_allows_out_for_delivery_to_done(): void
    {
        self::assertTrue(
            OrderTransitionMatrix::canTransition('OUT_FOR_DELIVERY', 'DONE'),
        );
    }

    public function test_rejects_cancelled_as_transition(): void
    {
        self::assertFalse(
            OrderTransitionMatrix::canTransition('PROCESSING', 'CANCELLED'),
        );
    }

    public function test_rejects_invalid_skip_transition(): void
    {
        self::assertFalse(
            OrderTransitionMatrix::canTransition('PROCESSING', 'DONE'),
        );
    }
}
