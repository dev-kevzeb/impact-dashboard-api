<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CountryKpaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_country' => [
                'required',
                'integer',
                Rule::exists('country', 'id'),

                Rule::unique('country_kpa')
                ->where(fn($q) => 
                    $q->where('id_country', $this->id_country)
                    ->where('id_kpa', $this->id_kpa)
                ),
            ],

            'id_kpa' => [
                'required',
                'integer',
                Rule::exists('kpa', 'id'),
            ],
        ];
    }

public function messages(): array
{
    return [
        'id_country.required' => 'Country is required',
        'id_country.integer'  => 'Country must be a numeric ID',
        'id_country.exists'   => 'the specified country does not exist',

        'id_kpa.required' => 'KPA is mandatory',
        'id_kpa.integer'  => 'The KPA must be a numeric ID',
        'id_kpa.exists'   => 'The specified KPA does not exist',

        'id_country.unique' => 'The Country is already associated with the KPA',
    ];
}

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
