<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\RegisterUser;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    /**
     * Register
     *
     * Creates an Owner account and returns a Sanctum Bearer token.
     * Role and user_id from the client are ignored; every registration is Owner.
     *
     * @group Authentication
     *
     * @unauthenticated
     *
     * @bodyParam name string required Display name (max 255). Example: New Owner
     * @bodyParam email string required Unique email (max 255). Example: new@example.com
     * @bodyParam password string required Password (8–72 chars). Example: password1
     * @bodyParam password_confirmation string required Must match password. Example: password1
     *
     * @response 201 scenario="created" {"token": "1|plainTextToken", "user": {"id": 3, "name": "New Owner", "email": "new@example.com", "role": "owner"}}
     * @response 422 scenario="invalid" {"message":"The email has already been taken.","errors":{"email":["The email has already been taken."]}}
     */
    public function __invoke(RegisterRequest $request, RegisterUser $registerUser): JsonResponse
    {
        $result = $registerUser->handle(
            $request->validated('name'),
            $request->validated('email'),
            $request->validated('password'),
        );

        return response()->json([
            'token' => $result['token'],
            'user' => UserResource::make($result['user'])->resolve(),
        ], 201);
    }
}
