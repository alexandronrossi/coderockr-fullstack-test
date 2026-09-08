<?php

namespace App\Services\Investment;

use App\Domain\Investment\InvestmentValuation;
use App\Models\Investment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListInvestments
{
    public function __construct(private readonly InvestmentValuation $valuation = new InvestmentValuation) {}

    /**
     * @return LengthAwarePaginator<int, ValuedInvestment>
     */
    public function handle(User $actor, int $page, int $perPage): LengthAwarePaginator
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

        return $paginator;
    }
}
