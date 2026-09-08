<?php

namespace Tests\Unit\Domain;

use App\Domain\Investment\CompoundGainCalculator;
use App\Domain\Investment\InvalidInvestmentDate;
use App\Domain\Investment\Money;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class CompoundGainCalculatorTest extends TestCase
{
    public function test_same_day_has_zero_gain(): void
    {
        $created = CarbonImmutable::parse('2025-01-15');
        $result = (new CompoundGainCalculator)->evaluate(
            Money::fromDecimalString('1000.00'),
            $created,
            $created,
        );

        $this->assertSame(0, $result['completeMonths']);
        $this->assertSame('1000.00', $result['expectedBalance']->toDecimalString());
        $this->assertSame('0.00', $result['gain']->toDecimalString());
    }

    public function test_one_complete_civil_month_compounds_once(): void
    {
        $created = CarbonImmutable::parse('2025-01-15');
        $result = (new CompoundGainCalculator)->evaluate(
            Money::fromDecimalString('1000.00'),
            $created,
            CarbonImmutable::parse('2025-02-15'),
        );

        $this->assertSame(1, $result['completeMonths']);
        $this->assertSame('1005.20', $result['expectedBalance']->toDecimalString());
        $this->assertSame('5.20', $result['gain']->toDecimalString());
    }

    public function test_two_complete_months_compound_with_monthly_half_up(): void
    {
        $created = CarbonImmutable::parse('2025-01-15');
        $result = (new CompoundGainCalculator)->evaluate(
            Money::fromDecimalString('1000.00'),
            $created,
            CarbonImmutable::parse('2025-03-15'),
        );

        $this->assertSame(2, $result['completeMonths']);
        $this->assertSame('1010.43', $result['expectedBalance']->toDecimalString());
    }

    public function test_incomplete_month_does_not_pay(): void
    {
        $created = CarbonImmutable::parse('2025-01-15');
        $result = (new CompoundGainCalculator)->evaluate(
            Money::fromDecimalString('1000.00'),
            $created,
            CarbonImmutable::parse('2025-02-14'),
        );

        $this->assertSame(0, $result['completeMonths']);
        $this->assertSame('1000.00', $result['expectedBalance']->toDecimalString());
    }

    public function test_as_of_before_created_throws(): void
    {
        $this->expectException(InvalidInvestmentDate::class);

        (new CompoundGainCalculator)->evaluate(
            Money::fromDecimalString('1000.00'),
            CarbonImmutable::parse('2025-01-15'),
            CarbonImmutable::parse('2025-01-14'),
        );
    }
}
