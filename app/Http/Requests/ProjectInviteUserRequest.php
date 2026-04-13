<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectInviteUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => [
                'required',
                'integer',
                'exists:project,id',
            ],
            'country_user_role_id' => [
                'required',
                'integer',
                'exists:country_user_role,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.required' => 'The project ID is required.',
            'project_id.integer' => 'The project ID must be an integer.',
            'project_id.exists' => 'The selected project does not exist.',
            'country_user_role_id.required' => 'The country user role ID is required.',
            'country_user_role_id.integer' => 'The country user role ID must be an integer.',
            'country_user_role_id.exists' => 'The selected country user role does not exist.',
        ];
    }
}