<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class CurrentUserController extends Controller
{
    /**
     * Current user
     *
     * Returns the profile of the authenticated token owner. Client-supplied ids are ignored.
     *
     * @group Authentication
     *
     * @authenticated
     *
     * @response 200 scenario="ok" {"data": {"id": 1, "name": "Owner", "email": "owner@example.com", "role": "owner"}}
     */
    public function __invoke(Request $request): UserResource
    {
        return UserResource::make($request->user());
    }
}
