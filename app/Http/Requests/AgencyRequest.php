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
                'required',
                'string',
                'url',
                'regex:/^(http|https):\/\//i',
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
            'name.required' => 'el nombre de la agencia no debe ir vacio',
            'name.min'      => 'el nombre de la agencia debe tener al menos 2 caracteres',
            'name.max'      => 'el nombre de la agencia no debe exceder 100 caracteres',

            'url.required' => 'la URL de la agencia no debe ir vacia',
            'url.url'      => 'la URL de la agencia debe tener un formato válido',
            'url.regex'    => 'la URL de la agencia debe usar protocolo HTTP o HTTPS',

            'is_approved.required' => 'el estado de aprobación es obligatorio',
            'is_approved.boolean'  => 'el estado de aprobación debe ser un valor booleano',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
