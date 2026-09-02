<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Orders;

use Cafeteria\Domain\Orders\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function test_from_string_normalizes_two_decimal_places(): void
    {
        self::assertSame('15.00', Money::fromString('15')->toString());
        self::assertSame('15.00', Money::fromString('15.00')->toString());
        self::assertSame('12.50', Money::fromString('12.5')->toString());
    }

    public function test_add_and_multiply_keep_scale_two(): void
    {
        $left = Money::fromString('10.00');
        $right = Money::fromString('5.50');

        self::assertSame('15.50', $left->add($right)->toString());
        self::assertSame('20.00', $left->multiply(2)->toString());
        self::assertSame('27.50', $right->multiply(5)->toString());
    }

    #[DataProvider('invalidAmountProvider')]
    public function test_from_string_rejects_invalid_amounts(string $amount): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Money::fromString($amount);
    }

    /**
     * @return list<array{string}>
     */
    public static function invalidAmountProvider(): array
    {
        return [
            [''],
            ['abc'],
            ['-1.00'],
            ['0.00'],
            ['10.999'],
        ];
    }
}
