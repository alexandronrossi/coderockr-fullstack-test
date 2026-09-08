<?php

namespace App\Services\Investment;

use App\Domain\Investment\InvalidInvestmentDate;
use App\Domain\Investment\InvestmentAlreadyWithdrawn;
use App\Domain\Investment\InvestmentValuation;
use App\Models\Investment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class WithdrawInvestment
{
    public function __construct(private readonly InvestmentValuation $valuation = new InvestmentValuation) {}

    public function handle(User $actor, int $id, string $withdrawnOn): ValuedInvestment
    {
        return DB::transaction(function () use ($actor, $id, $withdrawnOn): ValuedInvestment {
            $investment = Investment::query()
                ->visibleTo($actor)
                ->with('user')
                ->whereKey($id)
                ->lockForUpdate()
                ->first();

            if ($investment === null) {
                throw (new ModelNotFoundException)->setModel(Investment::class, [$id]);
            }

            if ($investment->withdrawn_on !== null) {
                throw new InvestmentAlreadyWithdrawn;
            }

            $created = $investment->createdOn();
            $withdrawn = CarbonImmutable::parse($withdrawnOn)->startOfDay();

            if ($withdrawn->lt($created)) {
                throw new InvalidInvestmentDate;
            }

            $investment->withdrawn_on = $withdrawn->toDateString();
            $investment->save();

            return ValuedInvestment::fromModel($investment, $withdrawn, $this->valuation);
        });
    }
}
