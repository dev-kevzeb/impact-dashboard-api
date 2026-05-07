<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CountryRequest extends FormRequest
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

        if ($this->has('currency.code')) {
            $code = strtoupper(trim($this->input('currency.code')));
            $this->merge([
                'currency' => [
                    ...$this->input('currency'),
                    'code' => $code,
                ],
            ]);
        }
    }

    public function rules(): array
    {
        $countryId = $this->route("id");

        return [
            "name" => [
                "required",
                "string",
                "min:2",
                "max:100",
                "regex:/^[a-zA-ZÀ-ÿñÑ\s\-\'\.]+$/u",
                Rule::unique("country", "name")->ignore($countryId),
            ],

            "currency" => [
                "required",
                "array",
            ],

            "currency.code" => [
                "required",
                "string",
                "min:3",
                "max:3",
                "regex:/^[A-Z]{3}$/",
            ],

            "currency.id" => [
                "nullable",
                "integer",
                Rule::exists("currency", "id"),
            ],

            "active" => $this->isMethod('POST') ? ["required", "boolean"] : ["prohibited"],
        ];
    }

    public function messages(): array
    {
        return [
            "name.required" => "The Country name is required",
            "name.min" => "The country name must be at least 2 characters",
            "name.max" => "The country name must not exceed 100 characters",
            "name.regex" => "The country name contains invalid characters",
            "name.unique" => "A country with that name already exists",

            "currency.required" => "The Currency is required",
            "currency.array" => "The Currency must be a valid array",

            "currency.code.required" => "The currency code is required",
            "currency.code.regex" => "The currency code must contain exactly 3 uppercase letters",
            "currency.code.min" => "Currency code must be 3 characters",
            "currency.code.max" => "Currency code must be 3 characters",

            "currency.id.integer" => "Currency ID must be a valid number",
            "currency.id.exists" => "The selected currency does not exist",

            "active.boolean" => "Active must be a boolean value",
            "active.required" => "The active field is required",
            "active.prohibited" => "The active field cannot be changed through this endpoint",
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
