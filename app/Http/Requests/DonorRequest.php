<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class DonorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Para update, excluir el registro actual de la validación unique
        $donorId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('donor', 'name')->ignore($donorId),
            ],

            'contribution' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],

            'project_id' => [
                'required',
                'integer',
                Rule::exists('project', 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // NAME
            'name.required' => 'El nombre del donante es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'name.max' => 'El nombre no debe exceder :max caracteres.',
            'name.unique' => 'Este donante ya existe en el sistema.',

            // CONTRIBUTION
            'contribution.required' => 'La contribución es obligatoria.',
            'contribution.numeric' => 'La contribución debe ser un número.',
            'contribution.min' => 'La contribución debe ser mínimo 0.',
            'contribution.max' => 'La contribución no debe exceder 100.',

            // PROJECT_ID
            'project_id.required' => 'El proyecto asociado es obligatorio.',
            'project_id.integer' => 'El ID del proyecto debe ser un número entero.',
            'project_id.exists' => 'El proyecto seleccionado no es válido.',
        ];
    }

    /**
     * Manejar validación fallida → JSON estándar
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
