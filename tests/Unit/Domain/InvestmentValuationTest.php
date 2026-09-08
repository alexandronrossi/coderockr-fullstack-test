<?php

namespace Tests\Unit\Domain;

use App\Domain\Investment\InvalidInvestmentDate;
use App\Domain\Investment\InvestmentValuation;
use App\Domain\Investment\Money;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class InvestmentValuationTest extends TestCase
{
    public function test_without_withdrawal_uses_as_of_date(): void
    {
        $created = CarbonImmutable::parse('2025-01-15');
        $result = (new InvestmentValuation)->evaluate(
            Money::fromDecimalString('1000.00'),
            $created,
            CarbonImmutable::parse('2025-02-15'),
            null,
        );

        $this->assertSame('2025-02-15', $result['effectiveAsOf']->toDateString());
        $this->assertSame(1, $result['completeMonths']);
        $this->assertSame('1005.20', $result['expectedBalance']->toDecimalString());
    }

    public function test_withdrawn_investment_freezes_gains_on_withdrawal_date(): void
    {
        $created = CarbonImmutable::parse('2025-01-15');
        $withdrawnOn = CarbonImmutable::parse('2025-02-15');
        $frozen = (new InvestmentValuation)->evaluate(
            Money::fromDecimalString('1000.00'),
            $created,
            $withdrawnOn,
            $withdrawnOn,
        );
        $later = (new InvestmentValuation)->evaluate(
            Money::fromDecimalString('1000.00'),
            $created,
            CarbonImmutable::parse('2025-06-15'),
            $withdrawnOn,
        );

        $this->assertSame($frozen['expectedBalance']->cents(), $later['expectedBalance']->cents());
        $this->assertSame($frozen['gain']->cents(), $later['gain']->cents());
        $this->assertSame('2025-02-15', $later['effectiveAsOf']->toDateString());
        $this->assertSame(1, $later['completeMonths']);
    }

    public function test_withdrawal_before_creation_throws(): void
    {
        $this->expectException(InvalidInvestmentDate::class);

        (new InvestmentValuation)->evaluate(
            Money::fromDecimalString('1000.00'),
            CarbonImmutable::parse('2025-01-15'),
            CarbonImmutable::parse('2025-02-15'),
            CarbonImmutable::parse('2025-01-01'),
        );
    }
}
