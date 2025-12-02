<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class KpaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $kpaId = $this->route("id");

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('kpa', 'name')->ignore($kpaId),
            ],

            'implementation' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // NAME
            'name.required' => 'el nombre del KPA no debe ir vacio',
            'name.min'      => 'el nombre del KPA debe tener al menos 2 caracteres',
            'name.max'      => 'el nombre del KPA no debe exceder 100 caracteres',
            'name.string'   => 'el nombre del KPA debe ser una cadena de texto',
            'name.unique'   => 'Ya existe un KPA con ese nombre',

            // IMPLEMENTATION
            'implementation.required' => 'la implementación del KPA debe ser un número',
            'implementation.numeric'  => 'la implementación del KPA debe ser un número',
            'implementation.min'      => 'la implementación del KPA debe estar entre 0 y 100',
            'implementation.max'      => 'la implementación del KPA debe estar entre 0 y 100',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
