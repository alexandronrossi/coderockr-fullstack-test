<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LogoutController extends Controller
{
    /**
     * Sign out
     *
     * Revokes the current Bearer token.
     *
     * @group Authentication
     *
     * @authenticated
     *
     * @response 204 scenario="ok"
     */
    public function __invoke(Request $request): Response
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->noContent();
    }
}
