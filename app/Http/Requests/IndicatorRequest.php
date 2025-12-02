<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class IndicatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [ 'required', 'string', 'min:2', 'max:200'],

            'target' => [ 'required', 'numeric', 'gt:0'],

            'type_id' => [ 'required', 'integer', Rule::exists('indicator_type', 'id')],

            'measure_id' => [ 'required', 'integer', Rule::exists('measure', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'el nombre del indicador no debe ir vacio',
            'name.min' => 'el nombre del indicador debe tener al menos 2 caracteres',
            'name.max' => 'el nombre del indicador no debe exceder 200 caracteres',

            'target.required' => 'el target es obligatorio',
            'target.numeric' => 'el target debe ser un número',
            'target.gt' => 'el target del indicador debe ser un número positivo',

            'type_id.required' => 'el tipo de indicador es obligatorio',
            'type_id.integer' => 'el tipo de indicador debe ser un ID numérico',
            'type_id.exists' => 'el tipo de indicador especificado no existe',

            'measure_id.required' => 'la medida es obligatoria',
            'measure_id.integer' => 'la medida debe ser un ID numérico',
            'measure_id.exists' => 'la medida especificada no existe',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
