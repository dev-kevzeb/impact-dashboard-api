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
            'name.required' => 'The name of the strategic result should not be empty',
            'name.min'      => 'The name of the strategic result must be at least 3 characters',
            'name.max'      => 'The name of the strategic result should not exceed 200 characters',
            'name.string'   => 'The name of the strategic result must be a text string',

            'id_ck.required' => 'id_ck is required',
            'id_ck.integer'  => 'The id_ck must be a valid numeric ID',
            'id_ck.exists'   => 'the specified country_kpa does not exist',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
