<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgramUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $assignmentId = $this->route('id');

        return [
            'program_id' => [
                'required',
                'integer',
                'exists:program,id'
            ],
            'country_kpa_user_id' => [
                'required',
                'integer',
                'exists:country_kpa_user,id',
                Rule::unique('program_user', 'country_kpa_user_id')
                    ->where('program_id', $this->program_id)
                    ->ignore($assignmentId)
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'program_id.required' => 'El ID del programa es obligatorio.',
            'program_id.integer' => 'El ID del programa debe ser un número entero.',
            'program_id.exists' => 'El programa seleccionado no existe.',
            
            'country_kpa_user_id.required' => 'El ID de la asignación CountryKpaUser es obligatorio.',
            'country_kpa_user_id.integer' => 'El ID debe ser un número entero.',
            'country_kpa_user_id.exists' => 'La asignación CountryKpaUser seleccionada no existe.',
            'country_kpa_user_id.unique' => 'Esta asignación ya existe para este programa.',
        ];
    }
}
