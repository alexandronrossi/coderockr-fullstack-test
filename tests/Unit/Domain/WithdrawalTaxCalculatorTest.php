<?php

namespace Tests\Unit\Domain;

use App\Domain\Investment\Money;
use App\Domain\Investment\WithdrawalTaxCalculator;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class WithdrawalTaxCalculatorTest extends TestCase
{
    public function test_canonical_example_under_one_year(): void
    {
        $created = CarbonImmutable::parse('2024-06-01');
        $result = (new WithdrawalTaxCalculator)->evaluate(
            Money::fromDecimalString('1000.00'),
            Money::fromDecimalString('1200.00'),
            $created,
            $created->addMonthsNoOverflow(6),
        );

        $this->assertSame('200.00', $result['gain']->toDecimalString());
        $this->assertSame('0.225', $result['rate']);
        $this->assertSame('45.00', $result['tax']->toDecimalString());
        $this->assertSame('1155.00', $result['net']->toDecimalString());
    }

    public function test_one_year_inclusive_uses_eighteen_point_five_percent(): void
    {
        $created = CarbonImmutable::parse('2023-06-01');
        $result = (new WithdrawalTaxCalculator)->evaluate(
            Money::fromDecimalString('1000.00'),
            Money::fromDecimalString('1200.00'),
            $created,
            $created->addYearsNoOverflow(1),
        );

        $this->assertSame('0.185', $result['rate']);
        $this->assertSame('37.00', $result['tax']->toDecimalString());
        $this->assertSame('1163.00', $result['net']->toDecimalString());
    }

    public function test_two_years_inclusive_uses_eighteen_point_five_percent(): void
    {
        $created = CarbonImmutable::parse('2022-06-01');
        $result = (new WithdrawalTaxCalculator)->evaluate(
            Money::fromDecimalString('1000.00'),
            Money::fromDecimalString('1200.00'),
            $created,
            $created->addYearsNoOverflow(2),
        );

        $this->assertSame('0.185', $result['rate']);
        $this->assertSame('37.00', $result['tax']->toDecimalString());
    }

    public function test_older_than_two_years_uses_fifteen_percent(): void
    {
        $created = CarbonImmutable::parse('2022-06-01');
        $result = (new WithdrawalTaxCalculator)->evaluate(
            Money::fromDecimalString('1000.00'),
            Money::fromDecimalString('1200.00'),
            $created,
            $created->addYearsNoOverflow(2)->addDay(),
        );

        $this->assertSame('0.15', $result['rate']);
        $this->assertSame('30.00', $result['tax']->toDecimalString());
        $this->assertSame('1170.00', $result['net']->toDecimalString());
    }

    public function test_zero_gain_has_zero_tax(): void
    {
        $created = CarbonImmutable::parse('2025-01-01');
        $principal = Money::fromDecimalString('1000.00');
        $result = (new WithdrawalTaxCalculator)->evaluate(
            $principal,
            $principal,
            $created,
            $created,
        );

        $this->assertSame('0.00', $result['tax']->toDecimalString());
        $this->assertSame('1000.00', $result['net']->toDecimalString());
    }
}
