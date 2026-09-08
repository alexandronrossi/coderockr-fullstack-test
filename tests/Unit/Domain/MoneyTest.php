<?php

namespace Tests\Unit\Domain;

use App\Domain\Investment\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_parses_and_formats_two_decimal_reais(): void
    {
        $money = Money::fromDecimalString('1000.00');

        $this->assertSame(100_000, $money->cents());
        $this->assertSame('1000.00', $money->toDecimalString());
    }

    public function test_applies_monthly_compound_rate_half_up_to_cents(): void
    {
        $result = Money::fromDecimalString('1000.00')->applyMonthlyCompoundRate();

        $this->assertSame(100_520, $result->cents());
        $this->assertSame('1005.20', $result->toDecimalString());
    }

    public function test_rejects_negative_cents(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromCents(-1);
    }
}
