<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class BeneficiaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Para update, excluir el registro actual de la validación unique
        $beneficiaryId = $this->route('id');
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('beneficiary', 'name')->ignore($beneficiaryId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del beneficiario es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no debe exceder :max caracteres.',
            'name.unique' => 'Este beneficiario ya existe en el sistema.',
        ];
    }

    /**
     * Manejo de validación fallida
     * Retorna respuesta JSON con errores en formato estandarizado
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
