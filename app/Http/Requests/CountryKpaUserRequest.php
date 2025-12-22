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
            'user_id' => 'required|integer|exists:user,id',
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
            
            'user_id.required' => 'The User ID is required.',
            'user_id.integer' => 'The User ID must be an integer.',
            'user_id.exists' => 'The specified User does not exist.',
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
                'data' => [
                    'errors' => $validator->errors()
                ]
            ], 422)
        );
    }
}
