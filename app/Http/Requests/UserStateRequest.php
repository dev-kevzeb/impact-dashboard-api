<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserStateRequest extends FormRequest
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
     */
    public function rules(): array
    {
        // Para update, excluir el registro actual de la validación unique
        $userStateId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                Rule::unique('user_state', 'name')->ignore($userStateId)
            ]
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del estado de usuario es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'name.max' => 'El nombre no debe exceder :max caracteres.',
            'name.unique' => 'Este estado de usuario ya existe en el sistema.',
        ];
    }
}
