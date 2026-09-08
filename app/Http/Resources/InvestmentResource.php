<?php

namespace App\Http\Resources;

use App\Services\Investment\ValuedInvestment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ValuedInvestment
 */
class InvestmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ValuedInvestment $valued */
        $valued = $this->resource;
        $investment = $valued->investment;
        $valuation = $valued->valuation;

        return [
            'id' => $investment->id,
            'owner' => [
                'id' => $investment->user->id,
                'name' => $investment->user->name,
                'email' => $investment->user->email,
            ],
            'amount' => $investment->principal()->toDecimalString(),
            'created_on' => $investment->createdOn()->toDateString(),
            'status' => $investment->status(),
            'withdrawn_on' => $investment->withdrawnOn()?->toDateString(),
            'expected_balance' => $valuation['expectedBalance']->toDecimalString(),
            'gain' => $valuation['gain']->toDecimalString(),
            'tax' => $valuation['tax']->toDecimalString(),
            'net' => $valuation['net']->toDecimalString(),
            'rate' => $valuation['rate'],
            'complete_months' => $valuation['completeMonths'],
        ];
    }
}
