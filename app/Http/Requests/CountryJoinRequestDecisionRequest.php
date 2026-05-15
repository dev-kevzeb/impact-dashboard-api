<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CountryJoinRequestDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => [
                'required',
                'string',
                'in:approve,revoke',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'The action is required.',
            'action.in' => 'The action must be one of: approve, revoke.',
        ];
    }
}
