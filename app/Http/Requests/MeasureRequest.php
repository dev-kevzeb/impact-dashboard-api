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
            'name.required' => 'The name of the measure should not be empty',
            'name.string'   => 'The measure name must be a text string',
            'name.min'      => 'Measure name must be at least 2 characters',
            'name.max'      => 'The measure name must not exceed 150 characters',

            'strategic_output_id.required' => 'The strategic result is mandatory',
            'strategic_output_id.integer'  => 'The strategic result must be a numeric ID',
            'strategic_output_id.exists'   => 'The specified strategic result does not exist',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
