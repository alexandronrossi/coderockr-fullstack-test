<?php

namespace App\Services\Investment;

use App\Domain\Investment\InvestmentValuation;
use App\Domain\Investment\Money;
use App\Models\Investment;
use Carbon\CarbonImmutable;

final class ValuedInvestment
{
    /**
     * @param  array{
     *     effectiveAsOf: CarbonImmutable,
     *     completeMonths: int,
     *     expectedBalance: Money,
     *     gain: Money,
     *     rate: string,
     *     tax: Money,
     *     net: Money
     * }  $valuation
     */
    public function __construct(
        public readonly Investment $investment,
        public readonly array $valuation,
    ) {}

    public static function fromModel(Investment $investment, CarbonImmutable $asOf, InvestmentValuation $valuation): self
    {
        $investment->loadMissing('user');

        return new self(
            $investment,
            $valuation->evaluate(
                $investment->principal(),
                $investment->createdOn(),
                $asOf,
                $investment->withdrawnOn(),
            ),
        );
    }
}
