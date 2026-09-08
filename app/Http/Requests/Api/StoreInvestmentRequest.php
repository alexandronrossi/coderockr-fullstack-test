<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvestmentRequest extends FormRequest
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
            'amount' => ['required', 'string', 'regex:/^\d+\.\d{2}$/', 'not_in:0.00'],
            'created_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ];
    }
}
