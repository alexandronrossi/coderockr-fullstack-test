<?php

namespace App\Services\Investment;

use App\Domain\Investment\InvestmentValuation;
use App\Domain\Investment\Money;
use App\Models\Investment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

final class CreateInvestment
{
    public function __construct(private readonly InvestmentValuation $valuation = new InvestmentValuation) {}

    public function handle(User $actor, string $amount, string $createdOn): ValuedInvestment
    {
        $principal = Money::fromDecimalString($amount);

        if ($principal->cents() === 0) {
            throw ValidationException::withMessages([
                'amount' => ['The amount must be greater than 0.00.'],
            ]);
        }

        $created = CarbonImmutable::parse($createdOn)->startOfDay();

        $investment = new Investment;
        $investment->user()->associate($actor);
        $investment->amount_cents = $principal->cents();
        $investment->created_on = $created->toDateString();
        $investment->save();

        return ValuedInvestment::fromModel($investment, $created, $this->valuation);
    }
}
