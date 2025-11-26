<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // NAME — requerido, min 3, max 255
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],

            // DESCRIPTION — obligatoria, min 10, max 2000
            'description' => [
                'required',
                'string',
                'min:10',
                'max:2000',
            ],

            // URL — opcional, pero si existe debe ser válida y con protocolo http/https
            'project_url' => [
                'nullable',
                'string',
                'max:255',
                'url',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        $scheme = parse_url($value, PHP_URL_SCHEME);
                        if (!in_array($scheme, ['http', 'https'])) {
                            $fail('La URL debe usar protocolo HTTP o HTTPS.');
                        }
                    }
                }
            ],

            // START DATE — obligatoria y formato válido
            'start_date' => [
                'required',
                'date_format:Y-m-d',
            ],

            // END DATE — obligatoria y >= start_date
            'end_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],

            // PROGRESS — 0 a 100
            'progress' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],

            // COMMENTS — opcionales, máximo 1000
            'comments' => [
                'nullable',
                'string',
                'max:1000',
            ],

            // BUDGET — obligatorio, numérico, > 0
            'project_budget' => [
                'required',
                'numeric',
                'gt:0', // mayor a 0
            ],

            // CONTACT
            'contact_id' => [
                'required',
                'integer',
                Rule::exists('contact', 'id'),
            ],

            // BENEFICIARY
            'beneficiary_id' => [
                'required',
                'integer',
                Rule::exists('beneficiary', 'id'),
            ],

            // PROJECT STATE
            'project_state_id' => [
                'required',
                'integer',
                Rule::exists('project_state', 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // Nombre
            'name.required' => 'El nombre del proyecto es obligatorio.',
            'name.min' => 'El nombre del proyecto debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre del proyecto no debe exceder 255 caracteres.',

            // Descripción
            'description.required' => 'La descripción del proyecto es obligatoria.',
            'description.min' => 'La descripción debe tener al menos 10 caracteres.',
            'description.max' => 'La descripción no debe exceder 2000 caracteres.',

            // URL
            'project_url.url' => 'La URL del proyecto debe tener un formato válido.',
            'project_url.max' => 'La URL no debe exceder 255 caracteres.',

            // Fechas
            'start_date.required' => 'La fecha de inicio es obligatoria.',
            'start_date.date_format' => 'La fecha de inicio debe tener formato YYYY-MM-DD.',

            'end_date.required' => 'La fecha de fin es obligatoria.',
            'end_date.date_format' => 'La fecha de fin debe tener formato YYYY-MM-DD.',
            'end_date.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',

            // Progreso
            'progress.required' => 'El progreso del proyecto es obligatorio.',
            'progress.numeric' => 'El progreso debe ser un valor numérico.',
            'progress.min' => 'El progreso mínimo es 0.',
            'progress.max' => 'El progreso máximo es 100.',

            // Comentarios
            'comments.max' => 'Los comentarios no deben exceder 1000 caracteres.',

            // Presupuesto
            'project_budget.required' => 'El presupuesto es obligatorio.',
            'project_budget.numeric' => 'El presupuesto debe ser numérico.',
            'project_budget.gt' => 'El presupuesto debe ser un número mayor a 0.',

            // Contacto
            'contact_id.required' => 'El contacto es obligatorio.',
            'contact_id.exists' => 'El contacto seleccionado no existe.',

            // Beneficiario
            'beneficiary_id.required' => 'El beneficiario es obligatorio.',
            'beneficiary_id.exists' => 'El beneficiario seleccionado no existe.',

            // Estado del proyecto
            'project_state_id.required' => 'El estado del proyecto es obligatorio.',
            'project_state_id.exists' => 'El estado del proyecto no existe.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
