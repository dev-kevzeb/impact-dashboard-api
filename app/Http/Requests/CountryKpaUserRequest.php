<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CountryKpaUserRequest extends FormRequest
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
        return [
            'country_kpa_id' => 'required|integer|exists:country_kpa,id',
            'user_role_id' => 'required|integer|exists:user_role,id',
            'required_role_name' => 'required|string|max:50',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'country_kpa_id.required' => 'The CountryKpa ID is required.',
            'country_kpa_id.integer' => 'The CountryKpa ID must be an integer.',
            'country_kpa_id.exists' => 'The specified CountryKpa does not exist.',
            
            'user_role_id.required' => 'The UserRole ID is required.',
            'user_role_id.integer' => 'The UserRole ID must be an integer.',
            'user_role_id.exists' => 'The specified UserRole does not exist.',
            
            'required_role_name.required' => 'The role name to validate is required.',
            'required_role_name.string' => 'The required role name must be a string.',
            'required_role_name.max' => 'The required role name must not exceed 50 characters.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
