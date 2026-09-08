<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\LoginUser;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    /**
     * Sign in
     *
     * Authenticates with email and password and returns a Sanctum Bearer token.
     * Extra fields such as role or user_id are ignored.
     *
     * @group Authentication
     *
     * @unauthenticated
     *
     * @bodyParam email string required User email. Example: owner@example.com
     * @bodyParam password string required User password. Example: password
     *
     * @response 200 scenario="ok" {"token": "1|plainTextToken", "user": {"id": 1, "name": "Owner", "email": "owner@example.com", "role": "owner"}}
     * @response 401 scenario="invalid" {"message": "Invalid credentials."}
     */
    public function __invoke(LoginRequest $request, LoginUser $loginUser): JsonResponse
    {
        $result = $loginUser->handle(
            $request->validated('email'),
            $request->validated('password'),
        );

        return response()->json([
            'token' => $result['token'],
            'user' => UserResource::make($result['user'])->resolve(),
        ]);
    }
}
