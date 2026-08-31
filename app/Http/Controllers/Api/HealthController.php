<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * Check API health
     *
     * Returns a simple payload so clients and Scribe can verify the API is reachable.
     *
     * @group System
     *
     * @unauthenticated
     *
     * @response 200 scenario="ok" {"status": "ok"}
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
        ]);
    }
}
