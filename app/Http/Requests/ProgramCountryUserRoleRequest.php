<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgramCountryUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $assignmentId = $this->route('id');

        return [
            'program_id' => [
                'required',
                'integer',
                'exists:program,id',
            ],
            'country_user_role_id' => [
                'required',
                'integer',
                'exists:country_user_role,id',
                Rule::unique('program_country_user_role', 'country_user_role_id')
                    ->where('program_id', $this->program_id)
                    ->ignore($assignmentId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'program_id.required'              => 'The program ID is required.',
            'program_id.integer'               => 'The program ID must be an integer.',
            'program_id.exists'                => 'The selected program does not exist.',
            'country_user_role_id.required'    => 'The country user role ID is required.',
            'country_user_role_id.integer'     => 'The country user role ID must be an integer.',
            'country_user_role_id.exists'      => 'The selected country user role does not exist.',
            'country_user_role_id.unique'      => 'This program is already assigned to this country user role.',
        ];
    }
}
