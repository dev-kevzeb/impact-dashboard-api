<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currencyId = $this->route("id");

        return [
            'code' => [
                'required',
                'string',
                'size:3',
                'regex:/^[A-Z]{3}$/',
                Rule::unique('currency', 'code')->ignore($currencyId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'The currency code must not be empty',
            'code.string'   => 'The currency code must contain only uppercase letters (no numbers or symbols)',
            'code.regex'    => 'The currency code must contain only uppercase letters (no numbers or symbols)',
            'code.size'     => 'The currency code must be exactly 3 characters',
            'code.unique'   => 'This currency already exists in the system',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray()),
        );
    }
}
