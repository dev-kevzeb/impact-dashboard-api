<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class IndicatorTypeRequest extends FormRequest
{
    public function authorize():bool
    {
        return true;
    }
    public function rules(): array
    {
        $indicatorTypeId = $this->route("id");
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('indicator_type', 'name')->ignore($indicatorTypeId),
            ],
            'is_bottom_up' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'=> 'el nombre del tipo de indicador no debe ir vacio',
            'name.min' => 'el nombre del tipo de indicador debe tener al menos 2 caracteres',
            'name.max' => 'el nombre del tipo de indicador no debe exceder 100 caracteres',
            'name.unique' => 'Ya existe un tipo de indicador con ese nombre',
            'name.string' => 'El nombre debe ser una cadena de texto',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray()),
        );
    }
}
