<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\WithdrawInvestmentRequest;
use App\Http\Resources\InvestmentResource;
use App\Services\Investment\WithdrawInvestment;

class WithdrawInvestmentController extends Controller
{
    /**
     * Withdraw investment
     *
     * Full withdrawal on a civil date. Tax is computed on the gain only. Extra
     * fields such as tax, net, expected_balance, or rate are ignored.
     *
     * @group Investments
     *
     * @authenticated
     *
     * @urlParam investment integer required Investment id. Example: 1
     *
     * @bodyParam withdrawn_on date required Withdrawal date (today or past, not before creation). Example: 2025-02-15
     *
     * @response 200 scenario="withdrawn" {"data":{"id":1,"status":"withdrawn","expected_balance":"1005.20","tax":"1.17","net":"1004.03"}}
     * @response 401 scenario="unauthenticated" {"message":"Unauthenticated."}
     * @response 404 scenario="missing or forbidden" {"message":"No query results for model [App\\Models\\Investment] 1"}
     * @response 422 scenario="already withdrawn or invalid date" {"message":"This investment has already been withdrawn."}
     */
    public function __invoke(WithdrawInvestmentRequest $request, int $investment, WithdrawInvestment $withdrawInvestment): InvestmentResource
    {
        $valued = $withdrawInvestment->handle(
            $request->user(),
            $investment,
            $request->validated('withdrawn_on'),
        );

        $this->authorize('withdraw', $valued->investment);

        return InvestmentResource::make($valued);
    }
}
