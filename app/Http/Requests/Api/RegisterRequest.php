<?php

namespace App\Http\Requests\Api;

use App\Support\Auth\AuthFieldLimits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:'.AuthFieldLimits::NAME_MAX],
            'email' => ['required', 'string', 'email', 'max:'.AuthFieldLimits::EMAIL_MAX, 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'confirmed',
                'max:'.AuthFieldLimits::PASSWORD_MAX,
                Password::min(AuthFieldLimits::PASSWORD_MIN),
            ],
        ];
    }
}
