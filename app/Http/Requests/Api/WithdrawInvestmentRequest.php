<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawInvestmentRequest extends FormRequest
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
            'withdrawn_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ];
    }
}
