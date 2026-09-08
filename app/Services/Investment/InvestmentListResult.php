<?php

namespace App\Services\Investment;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class InvestmentListResult
{
    /**
     * @param  LengthAwarePaginator<int, ValuedInvestment>  $page
     * @param  array{total_balance: string}  $summary
     */
    public function __construct(
        public readonly LengthAwarePaginator $page,
        public readonly array $summary,
    ) {}
}
