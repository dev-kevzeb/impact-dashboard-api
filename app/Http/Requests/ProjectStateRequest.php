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
            'state' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'state.required' => 'El estado del proyecto es obligatorio.',
            'state.string' => 'El estado debe ser una cadena de texto.',
            'state.max' => 'El estado no debe exceder :max caracteres.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
