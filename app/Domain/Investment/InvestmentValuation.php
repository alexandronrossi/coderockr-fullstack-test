<?php

namespace App\Domain\Investment;

use Carbon\CarbonImmutable;

final class InvestmentValuation
{
    public function __construct(
        private readonly CompoundGainCalculator $gainCalculator = new CompoundGainCalculator,
        private readonly WithdrawalTaxCalculator $taxCalculator = new WithdrawalTaxCalculator,
    ) {}

    /**
     * @return array{
     *     effectiveAsOf: CarbonImmutable,
     *     completeMonths: int,
     *     expectedBalance: Money,
     *     gain: Money,
     *     rate: string,
     *     tax: Money,
     *     net: Money
     * }
     */
    public function evaluate(
        Money $principal,
        CarbonImmutable $createdOn,
        CarbonImmutable $asOf,
        ?CarbonImmutable $withdrawnOn,
    ): array {
        $createdOn = $createdOn->startOfDay();
        $asOf = $asOf->startOfDay();
        $withdrawnOn = $withdrawnOn?->startOfDay();

        if ($withdrawnOn !== null && $withdrawnOn->lt($createdOn)) {
            throw new InvalidInvestmentDate;
        }

        $effectiveAsOf = $withdrawnOn === null || $asOf->lt($withdrawnOn)
            ? $asOf
            : $withdrawnOn;

        $gain = $this->gainCalculator->evaluate($principal, $createdOn, $effectiveAsOf);
        $tax = $this->taxCalculator->evaluate(
            $principal,
            $gain['expectedBalance'],
            $createdOn,
            $effectiveAsOf,
        );

        return [
            'effectiveAsOf' => $effectiveAsOf,
            'completeMonths' => $gain['completeMonths'],
            'expectedBalance' => $gain['expectedBalance'],
            'gain' => $gain['gain'],
            'rate' => $tax['rate'],
            'tax' => $tax['tax'],
            'net' => $tax['net'],
        ];
    }
}
