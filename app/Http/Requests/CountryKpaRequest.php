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
        'id_country.required' => 'el país es obligatorio',
        'id_country.integer'  => 'el país debe ser un ID numérico',
        'id_country.exists'   => 'el país especificado no existe',

        'id_kpa.required' => 'el KPA es obligatorio',
        'id_kpa.integer'  => 'el KPA debe ser un ID numérico',
        'id_kpa.exists'   => 'el KPA especificado no existe',

        'id_country.unique' => 'este país ya está asociado con este KPA',
    ];
}

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
