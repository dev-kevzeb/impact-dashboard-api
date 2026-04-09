<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'first_name' => [
                'required',
                'string',
                'min:2',
                'max:50',
            ],

            'last_name' => [
                'required',
                'string',
                'min:2',
                'max:50',
            ],

            'title' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:254',
            ],

            'phone' => [
                'required',
                'string',
                'regex:/^(\+?\d{1,4})?[\s\-]?\(?\d{1,4}\)?[\s\-]?\d+(\s?\-?\d+)*$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [

            'first_name.required' => 'The contact first name must not be empty',
            'first_name.min' => 'The contact first name must be at least 2 characters',
            'first_name.max' => 'The contact first name must not exceed 50 characters',
            'first_name.regex' => 'The contact first name contains invalid characters',

            'last_name.required' => 'The contact last name must not be empty',
            'last_name.min' => 'The contact last name must be at least 2 characters',
            'last_name.max' => 'The contact last name must not exceed 50 characters',
            'last_name.regex' => 'The contact last name contains invalid characters',

            'title.required' => 'The contact title must not be empty',
            'title.min' => 'The contact title must be at least 2 characters',
            'title.max' => 'The contact title must not exceed 100 characters',
            'title.regex' => 'The contact title contains invalid characters',

            'email.required' => 'The contact email must not be empty',
            'email.email' => 'The contact email must be a valid email address',
            'email.max' => 'The contact email must not exceed 254 characters',

            'phone.required' => 'The phone number is required',
            'phone.regex' => 'The phone number format is invalid - use international format',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
