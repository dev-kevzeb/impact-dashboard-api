<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('user', 'email')->ignore($userId)
            ],
            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'string',
                'min:6',
                'max:255'
            ],
            'user_state_id' => [
                'required',
                'integer',
                'exists:user_state,id'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The user name is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name must not exceed :max characters.',
            'email.required' => 'The user email is required.',
            'email.string' => 'The email must be a string.',
            'email.email' => 'The email must be a valid address.',
            'email.max' => 'The email must not exceed :max characters.',
            'email.unique' => 'This email is already registered in the system.',
            'password.required' => 'The password is required.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least :min characters.',
            'password.max' => 'The password must not exceed :max characters.',
            'user_state_id.required' => 'The user state is required.',
            'user_state_id.integer' => 'The state must be an integer.',
            'user_state_id.exists' => 'The selected state does not exist.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
