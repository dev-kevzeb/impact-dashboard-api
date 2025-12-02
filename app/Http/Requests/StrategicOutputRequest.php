<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StrategicOutputRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $outputId = $this->route("id");

        return [
            'name' => [
                'required',
                'string',
                'min:2', 
                'max:200',
                Rule::unique('strategic_output', 'name')->ignore($outputId),
            ],

            'id_ck' => [
                'required',
                'integer',
                Rule::exists('country_kpa', 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // NAME
            'name.required' => 'el nombre del resultado estratégico no debe ir vacío',
            'name.min'      => 'el nombre del resultado estratégico debe tener al menos 3 caracteres',
            'name.max'      => 'el nombre del resultado estratégico no debe exceder 200 caracteres',
            'name.unique'   => 'ya existe un resultado estratégico con este nombre',
            'name.string'   => 'el nombre del resultado estratégico debe ser una cadena de texto',

            // COUNTRY KPA (FK)
            'id_ck.required' => 'el id_ck es obligatorio',
            'id_ck.integer'  => 'el id_ck debe ser un ID numérico válido',
            'id_ck.exists'   => 'el country_kpa especificado no existe',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
