<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvestmentResource;
use App\Services\Investment\ShowInvestment;
use Illuminate\Http\Request;

class ShowInvestmentController extends Controller
{
    /**
     * Show investment
     *
     * Returns one visible investment with server-computed expected balance.
     * Owners receive 404 for another person's id (same as a missing id).
     *
     * @group Investments
     *
     * @authenticated
     *
     * @urlParam investment integer required Investment id. Example: 1
     *
     * @response 200 scenario="ok" {"data":{"id":1,"amount":"1000.00","status":"active","expected_balance":"1005.20"}}
     * @response 401 scenario="unauthenticated" {"message":"Unauthenticated."}
     * @response 404 scenario="missing or forbidden" {"message":"No query results for model [App\\Models\\Investment] 1"}
     */
    public function __invoke(Request $request, int $investment, ShowInvestment $showInvestment): InvestmentResource
    {
        $valued = $showInvestment->handle($request->user(), $investment);

        $this->authorize('view', $valued->investment);

        return InvestmentResource::make($valued);
    }
}
