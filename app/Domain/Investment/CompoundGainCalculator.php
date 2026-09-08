<?php

namespace App\Domain\Investment;

use Carbon\CarbonImmutable;

final class CompoundGainCalculator
{
    /**
     * @return array{completeMonths: int, expectedBalance: Money, gain: Money}
     */
    public function evaluate(Money $principal, CarbonImmutable $createdOn, CarbonImmutable $asOf): array
    {
        $createdOn = $createdOn->startOfDay();
        $asOf = $asOf->startOfDay();

        if ($asOf->lt($createdOn)) {
            throw new InvalidInvestmentDate;
        }

        $completeMonths = 0;

        while (CivilMonthAnniversary::of($createdOn, $completeMonths + 1)->lte($asOf)) {
            $completeMonths++;
        }

        $expectedBalance = $principal;

        for ($month = 0; $month < $completeMonths; $month++) {
            $expectedBalance = $expectedBalance->applyMonthlyCompoundRate();
        }

        return [
            'completeMonths' => $completeMonths,
            'expectedBalance' => $expectedBalance,
            'gain' => $expectedBalance->minus($principal),
        ];
    }
}
