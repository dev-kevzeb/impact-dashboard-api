<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'program_country_user_role_id' => [
                'required',
                'integer',
                'exists:program_country_user_role,id',
            ],
            'invited_user_role_id' => [
                'required',
                'integer',
                'exists:user_role,id',
                Rule::unique('invite_program', 'invited_user_role_id')
                    ->where('program_country_user_role_id', $this->program_country_user_role_id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'program_country_user_role_id.required' => 'The program country user role ID is required.',
            'program_country_user_role_id.integer' => 'The program country user role ID must be an integer.',
            'program_country_user_role_id.exists' => 'The selected program country user role does not exist.',
            'invited_user_role_id.required' => 'The invited user role ID is required.',
            'invited_user_role_id.integer' => 'The invited user role ID must be an integer.',
            'invited_user_role_id.exists' => 'The selected invited user role does not exist.',
            'invited_user_role_id.unique' => 'This user role is already invited to this program.',
        ];
    }
}
