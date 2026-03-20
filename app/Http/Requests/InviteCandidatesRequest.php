<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InviteCandidatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'program_country_user_role_id' => ['required', 'integer', 'exists:program_country_user_role,id'],
            'search' => ['nullable', 'string', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'program_country_user_role_id.required' => 'The program country user role ID is required.',
            'program_country_user_role_id.integer' => 'The program country user role ID must be an integer.',
            'program_country_user_role_id.exists' => 'The selected program country user role does not exist.',
            'search.string' => 'The search value must be a string.',
            'search.min' => 'The search value must be at least 1 character.',
            'per_page.integer' => 'The per page value must be an integer.',
            'per_page.min' => 'The per page value must be at least 1.',
            'per_page.max' => 'The per page value must not exceed 100.',
        ];
    }
}
