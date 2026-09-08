<?php

namespace App\Services\Investment;

use App\Domain\Investment\InvestmentValuation;
use App\Models\Investment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class ShowInvestment
{
    public function __construct(private readonly InvestmentValuation $valuation = new InvestmentValuation) {}

    public function handle(User $actor, int $id): ValuedInvestment
    {
        $investment = Investment::query()
            ->visibleTo($actor)
            ->with('user')
            ->find($id);

        if ($investment === null) {
            throw (new ModelNotFoundException)->setModel(Investment::class, [$id]);
        }

        return ValuedInvestment::fromModel($investment, CarbonImmutable::today(), $this->valuation);
    }
}
