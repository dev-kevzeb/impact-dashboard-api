<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CountryDashboardShareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country_id' => [
                'required',
                'integer',
                'exists:country,id',
            ],
            'shared_user_role_id' => [
                'required',
                'integer',
                'exists:user_role,id',
                Rule::unique('country_dashboard_share', 'shared_user_role_id')
                    ->where('country_id', $this->country_id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'country_id.required' => 'The country ID is required.',
            'country_id.integer' => 'The country ID must be an integer.',
            'country_id.exists' => 'The selected country does not exist.',
            'shared_user_role_id.required' => 'The shared user role ID is required.',
            'shared_user_role_id.integer' => 'The shared user role ID must be an integer.',
            'shared_user_role_id.exists' => 'The selected shared user role does not exist.',
            'shared_user_role_id.unique' => 'This admin is already approved for the selected country dashboard.',
        ];
    }
}
