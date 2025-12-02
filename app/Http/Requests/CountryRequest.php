<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $countryId = $this->route("id");

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-ZÀ-ÿñÑ\s\-\'\.]+$/u',
                Rule::unique('country', 'name')->ignore($countryId),
            ],

            'currency_id' => [
                'required',
                'integer',
                Rule::exists('currency', 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // NAME errors (idénticos al dominio)
            'name.required' => 'el nombre del país no debe ir vacio',
            'name.min' => 'el nombre del país debe tener al menos 2 caracteres',
            'name.max' => 'el nombre del país no debe exceder 100 caracteres',
            'name.regex' => 'el nombre del país contiene caracteres no válidos',
            'name.string' => 'el nombre del país contiene caracteres no válidos',
            'name.unique' => 'Ya existe un país con ese nombre',

            // CURRENCY errors
            'currency_id.required' => 'la moneda es obligatoria',
            'currency_id.integer' => 'la moneda debe ser un ID numérico válido',
            'currency_id.exists' => 'la moneda debe ser una instancia de Currency',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
