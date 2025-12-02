<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class MeasureRequest extends FormRequest
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
                'max:150',
            ],

            'strategic_output_id' => [
                'required',
                'integer',
                Rule::exists('strategic_output', 'id'),
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'el nombre de la medida no debe ir vacio',
            'name.string'   => 'el nombre de la medida debe ser una cadena de texto',
            'name.min'      => 'el nombre de la medida debe tener al menos 2 caracteres',
            'name.max'      => 'el nombre de la medida no debe exceder 150 caracteres',

            'strategic_output_id.required' => 'el resultado estratégico es obligatorio',
            'strategic_output_id.integer'  => 'el resultado estratégico debe ser un ID numérico',
            'strategic_output_id.exists'   => 'el resultado estratégico especificado no existe',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
