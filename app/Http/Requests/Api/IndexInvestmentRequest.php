<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class IndexInvestmentRequest extends FormRequest
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
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function page(): int
    {
        return (int) ($this->safe()->input('page') ?? 1);
    }

    public function perPage(): int
    {
        return (int) ($this->safe()->input('per_page') ?? 15);
    }
}
