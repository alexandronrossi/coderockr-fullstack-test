<?php

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Models\User;

class RegisterUser
{
    /**
     * @return array{token: string, user: User}
     */
    public function handle(string $name, string $email, string $password): array
    {
        $user = new User;
        $user->fill([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
        $user->role = UserRole::Owner;
        $user->save();

        return [
            'token' => $user->createToken('api')->plainTextToken,
            'user' => $user,
        ];
    }
}
