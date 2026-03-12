<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AgencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('name')) {
            $name = trim($this->input('name'));
            $name = preg_replace('/\s+/', ' ', $name);
            $this->merge([
                'name' => $name
            ]);
        }

        // Normalize url: null or missing becomes empty string
        $this->merge(['url' => trim((string) $this->input('url', ''))]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'url' => [
                'string',
                function ($attribute, $value, $fail) {
                    if ($value === '') return; // empty string is valid (url is optional)
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        $fail('Agency URL must be in valid format');
                    }
                    if (!preg_match('/^(http|https):\/\//i', $value)) {
                        $fail('The agency URL must use HTTP or HTTPS protocol');
                    }
                },
            ],

            'is_approved' => [
                'required',
                'boolean'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The agency name should not be empty',
            'name.min'      => 'Agency name must be at least 2 characters',
            'name.max'      => 'Agency name must not exceed 100 characters',

            'url.url'   => 'Agency URL must be in valid format',
            'url.regex' => 'The agency URL must use HTTP or HTTPS protocol',

            'is_approved.required' => 'Approval status is required',
            'is_approved.boolean'  => 'Approval status must be a boolean value',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
