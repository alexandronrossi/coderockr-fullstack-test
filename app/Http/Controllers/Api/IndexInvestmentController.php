<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexInvestmentRequest;
use App\Http\Resources\InvestmentResource;
use App\Models\Investment;
use App\Services\Investment\ListInvestments;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexInvestmentController extends Controller
{
    /**
     * List investments
     *
     * Paginated investments visible to the current user. Owners see only their own
     * records. Admins see every owner's investments. Query user_id is ignored.
     * `summary.total_balance` is the server-computed sum of expected balances for
     * all active investments in that same visibility scope (not only the current page).
     *
     * @group Investments
     *
     * @authenticated
     *
     * @queryParam page integer Page number. Example: 1
     * @queryParam per_page integer Items per page (1-100, default 15). Example: 15
     *
     * @response 200 scenario="ok" {"data":[],"links":{},"meta":{"current_page":1,"per_page":15,"total":0},"summary":{"total_balance":"0.00"}}
     * @response 401 scenario="unauthenticated" {"message":"Unauthenticated."}
     */
    public function __invoke(IndexInvestmentRequest $request, ListInvestments $listInvestments): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Investment::class);

        $result = $listInvestments->handle(
            $request->user(),
            $request->page(),
            $request->perPage(),
        );

        return InvestmentResource::collection($result->page)
            ->additional(['summary' => $result->summary]);
    }
}
