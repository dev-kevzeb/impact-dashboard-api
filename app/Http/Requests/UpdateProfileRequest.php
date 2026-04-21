<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['prohibited'],
            'user_state_id' => ['prohibited'],
            'role' => ['prohibited'],
            'roles' => ['prohibited'],
            'country_id' => ['prohibited'],
            'country_user_role' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name is required.',
            'name.string' => 'The name must be a string.',
            'name.min' => 'The name must be at least :min characters.',
            'name.max' => 'The name must not exceed :max characters.',
            'email.prohibited' => 'Email cannot be updated from profile.',
            'user_state_id.prohibited' => 'User state cannot be updated from profile.',
            'role.prohibited' => 'Role cannot be updated from profile.',
            'roles.prohibited' => 'Roles cannot be updated from profile.',
            'country_id.prohibited' => 'Country cannot be updated from profile.',
            'country_user_role.prohibited' => 'Country assignment cannot be updated from profile.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}