<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProjectStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'state' => 'required|string|min:3|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'state.required' => 'El estado del proyecto es obligatorio.',
            'state.string'   => 'El estado del proyecto debe ser un texto válido.',
            'state.min'      => 'El estado del proyecto debe tener al menos 3 caracteres.',
            'state.max'      => 'El estado del proyecto no debe exceder los 100 caracteres.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
