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
            'state.required' => 'Project status is required.',
            'state.string'   => 'Project status must be a valid string.',
            'state.min'      => 'Project status must be at least 3 characters long.',
            'state.max'      => 'Project status must not exceed 100 characters.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
