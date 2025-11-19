<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ProjectStateRequest extends FormRequest
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
        $projectStateId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($projectStateId) {
                    // Validación case-insensitive manual
                    $exists = DB::table('project_state')
                        ->whereRaw('LOWER(name) = ?', [strtolower($value)])
                        ->when($projectStateId, function ($query, $id) {
                            return $query->where('id', '!=', $id);
                        })
                        ->exists();

                    if ($exists) {
                        $fail('Este estado del proyecto ya existe en el sistema.');
                    }
                }
            ]
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
            'name.required' => 'El nombre del estado del proyecto es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no debe exceder :max caracteres.',
            'name.unique' => 'Este estado del proyecto ya existe en el sistema.',
        ];
    }
}
