<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CountryJoinRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country_id' => [
                'required',
                'integer',
                'exists:country,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'country_id.required' => 'The country ID is required.',
            'country_id.integer' => 'The country ID must be an integer.',
            'country_id.exists' => 'The selected country does not exist.',
        ];
    }
}
