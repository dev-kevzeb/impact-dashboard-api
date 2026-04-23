<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'max:255', 'confirmed'],
            'name' => ['prohibited'],
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
            'current_password.required' => 'The current password is required.',
            'current_password.string' => 'The current password must be a string.',
            'password.required' => 'The new password is required.',
            'password.string' => 'The new password must be a string.',
            'password.min' => 'The new password must be at least :min characters.',
            'password.max' => 'The new password must not exceed :max characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'name.prohibited' => 'Name cannot be updated from password change.',
            'email.prohibited' => 'Email cannot be updated from password change.',
            'user_state_id.prohibited' => 'User state cannot be updated from password change.',
            'role.prohibited' => 'Role cannot be updated from password change.',
            'roles.prohibited' => 'Roles cannot be updated from password change.',
            'country_id.prohibited' => 'Country cannot be updated from password change.',
            'country_user_role.prohibited' => 'Country assignment cannot be updated from password change.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}