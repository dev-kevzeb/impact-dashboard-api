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
                'regex:/^[a-zA-ZÀ-ÿñÑ\s\'\-\.,\/]+$/u',
            ],

            'last_name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                'regex:/^[a-zA-ZÀ-ÿñÑ\s\'\-\.,\/]+$/u',
            ],

            'title' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-ZÀ-ÿñÑ\s\'\-\.,\/]+$/u',
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

            'first_name.required' => 'el nombre del contacto no debe ir vacío',
            'first_name.min' => 'el nombre del contacto debe tener al menos 2 caracteres',
            'first_name.max' => 'el nombre del contacto no debe exceder 50 caracteres',
            'first_name.regex' => 'el nombre del contacto contiene caracteres no válidos',

            'last_name.required' => 'el apellido del contacto no debe ir vacío',
            'last_name.min' => 'el apellido del contacto debe tener al menos 2 caracteres',
            'last_name.max' => 'el apellido del contacto no debe exceder 50 caracteres',
            'last_name.regex' => 'el apellido del contacto contiene caracteres no válidos',

            'title.required' => 'el título del contacto no debe ir vacío',
            'title.min' => 'el título del contacto debe tener al menos 2 caracteres',
            'title.max' => 'el título del contacto no debe exceder 100 caracteres',
            'title.regex' => 'el título del contacto contiene caracteres no válidos',

            'email.required' => 'el email del contacto no debe ir vacío',
            'email.email' => 'el email del contacto debe tener un formato válido',
            'email.max' => 'el email del contacto excede la longitud máxima permitida (254 caracteres)',

            'phone.required' => 'el teléfono es obligatorio',
            'phone.regex' => 'el formato del teléfono no es válido - use formato internacional',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
