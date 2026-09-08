<?php

namespace App\Http\Requests\Api;

use App\Support\Auth\AuthFieldLimits;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:'.AuthFieldLimits::EMAIL_MAX],
            'password' => ['required', 'string', 'max:'.AuthFieldLimits::PASSWORD_MAX],
        ];
    }
}
