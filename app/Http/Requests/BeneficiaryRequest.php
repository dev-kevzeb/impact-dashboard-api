<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BeneficiaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'unique:beneficiary,name',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del beneficiario es obligatorio.',
            'name.string'   => 'El nombre debe ser una cadena de texto.',
            'name.min'      => 'El nombre del beneficiario debe tener al menos 2 caracteres.',
            'name.max'      => 'El nombre no debe exceder 255 caracteres.',
            'name.unique'   => 'este beneficiario ya existe en el sistema.',
        ];
    }


    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
