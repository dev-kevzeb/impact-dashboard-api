<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currencyId = $this->route("id");

        return [
            'code' => [
                'required',
                'string',
                'min:3',
                'max:3',
                'regex:/^[A-Za-z]+$/',
                Rule::unique('currency', 'code')->ignore($currencyId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'el código de moneda no debe ir vacío',
            'code.string'   => 'el código de moneda debe contener solo letras (sin números ni símbolos)',
            'code.regex'    => 'el código de moneda debe contener solo letras (sin números ni símbolos)',
            'code.min'      => 'el código de moneda debe tener exactamente 3 caracteres',
            'code.max'      => 'el código de moneda debe tener exactamente 3 caracteres',
            'code.unique'   => 'Esta moneda ya existe en el sistema',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray()),
        );
    }
}
