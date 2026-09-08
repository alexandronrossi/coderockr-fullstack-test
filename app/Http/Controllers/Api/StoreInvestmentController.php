<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreInvestmentRequest;
use App\Http\Resources\InvestmentResource;
use App\Models\Investment;
use App\Services\Investment\CreateInvestment;
use Illuminate\Http\JsonResponse;

class StoreInvestmentController extends Controller
{
    /**
     * Create investment
     *
     * Registers an investment owned by the authenticated user. Extra fields such as
     * user_id, status, expected_balance, tax, or withdrawn_on are ignored.
     *
     * @group Investments
     *
     * @authenticated
     *
     * @bodyParam amount string required Amount with two decimal places, greater than 0.00. Example: 1000.00
     * @bodyParam created_on date required Creation date (today or past). Example: 2025-01-15
     *
     * @response 201 scenario="created" {"data":{"id":1,"owner":{"id":2,"name":"Owner","email":"owner@example.com"},"amount":"1000.00","created_on":"2025-01-15","status":"active","withdrawn_on":null,"expected_balance":"1000.00","gain":"0.00","tax":"0.00","net":"1000.00","rate":"0.225","complete_months":0}}
     * @response 401 scenario="unauthenticated" {"message":"Unauthenticated."}
     * @response 403 scenario="admin" {"message":"This action is unauthorized."}
     * @response 422 scenario="invalid" {"message":"The amount field format is invalid.","errors":{"amount":["The amount field format is invalid."]}}
     */
    public function __invoke(StoreInvestmentRequest $request, CreateInvestment $createInvestment): JsonResponse
    {
        $this->authorize('create', Investment::class);

        $valued = $createInvestment->handle(
            $request->user(),
            $request->validated('amount'),
            $request->validated('created_on'),
        );

        return InvestmentResource::make($valued)
            ->response()
            ->setStatusCode(201);
    }
}
