<?php

namespace App\Services\Investment;

use App\Domain\Investment\InvestmentValuation;
use App\Domain\Investment\Money;
use App\Models\Investment;
use App\Models\User;
use Carbon\CarbonImmutable;

final class ListInvestments
{
    public function __construct(private readonly InvestmentValuation $valuation = new InvestmentValuation) {}

    public function handle(User $actor, int $page, int $perPage): InvestmentListResult
    {
        $today = CarbonImmutable::today();

        $paginator = Investment::query()
            ->visibleTo($actor)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $paginator->setCollection(
            $paginator->getCollection()->map(
                fn (Investment $investment): ValuedInvestment => ValuedInvestment::fromModel(
                    $investment,
                    $today,
                    $this->valuation,
                ),
            ),
        );

        return new InvestmentListResult(
            $paginator,
            $this->summaryFor($actor, $today),
        );
    }

    /**
     * @return array{total_balance: string}
     */
    private function summaryFor(User $actor, CarbonImmutable $asOf): array
    {
        $total = Money::fromCents(0);

        Investment::query()
            ->visibleTo($actor)
            ->whereNull('withdrawn_on')
            ->orderBy('id')
            ->cursor()
            ->each(function (Investment $investment) use (&$total, $asOf): void {
                $valued = ValuedInvestment::fromModel($investment, $asOf, $this->valuation);
                $total = $total->plus($valued->valuation['expectedBalance']);
            });

        return [
            'total_balance' => $total->toDecimalString(),
        ];
    }
}
