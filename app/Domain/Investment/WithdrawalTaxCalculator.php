<?php

namespace App\Domain\Investment;

use Carbon\CarbonImmutable;

final class WithdrawalTaxCalculator
{
    private const RATE_UNDER_ONE_YEAR = '0.225';

    private const RATE_ONE_TO_TWO_YEARS = '0.185';

    private const RATE_OVER_TWO_YEARS = '0.15';

    private const BPS_UNDER_ONE_YEAR = 2_250;

    private const BPS_ONE_TO_TWO_YEARS = 1_850;

    private const BPS_OVER_TWO_YEARS = 1_500;

    /**
     * @return array{gain: Money, rate: string, tax: Money, net: Money}
     */
    public function evaluate(Money $principal, Money $expectedBalance, CarbonImmutable $createdOn, CarbonImmutable $taxAsOf): array
    {
        $createdOn = $createdOn->startOfDay();
        $taxAsOf = $taxAsOf->startOfDay();
        $gain = $expectedBalance->minus($principal);
        $rate = $this->rateFor($createdOn, $taxAsOf);
        $tax = $gain->percentageOf($this->basisPointsFor($rate));
        $net = Money::fromCents($expectedBalance->cents() - $tax->cents());

        return [
            'gain' => $gain,
            'rate' => $rate,
            'tax' => $tax,
            'net' => $net,
        ];
    }

    private function rateFor(CarbonImmutable $createdOn, CarbonImmutable $taxAsOf): string
    {
        $oneYear = $createdOn->addYearsNoOverflow(1);
        $twoYears = $createdOn->addYearsNoOverflow(2);

        if ($taxAsOf->lt($oneYear)) {
            return self::RATE_UNDER_ONE_YEAR;
        }

        if ($taxAsOf->lte($twoYears)) {
            return self::RATE_ONE_TO_TWO_YEARS;
        }

        return self::RATE_OVER_TWO_YEARS;
    }

    private function basisPointsFor(string $rate): int
    {
        return match ($rate) {
            self::RATE_UNDER_ONE_YEAR => self::BPS_UNDER_ONE_YEAR,
            self::RATE_ONE_TO_TWO_YEARS => self::BPS_ONE_TO_TWO_YEARS,
            default => self::BPS_OVER_TWO_YEARS,
        };
    }
}
