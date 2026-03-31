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
            'name' => [ 'required', 'string', 'min:2', 'max:300'],

            'target' => [ 'required', 'numeric', 'gt:0'],

            'actual_value' => [ 'nullable', 'numeric', 'min:0'],

            'type_id' => [ 'required', 'integer', Rule::exists('indicator_type', 'id')],

            'measure_id' => [ 'required', 'integer', Rule::exists('measure', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The indicator name must not be empty.',
            'name.min' => 'The indicator name must be at least 2 characters long.',
            'name.max' => 'The indicator name must not exceed 300 characters.',

            'target.required' => 'The target is required.',
            'target.numeric' => 'The target must be a number.',
            'target.gt' => 'The indicator target must be a positive number.',

            'type_id.required' => 'The indicator type is required.',
            'type_id.integer' => 'The indicator type must be a numeric ID.',
            'type_id.exists' => 'The specified indicator type does not exist.',

            'measure_id.required' => 'The measure is required.',
            'measure_id.integer' => 'The measure must be a numeric ID.',
            'measure_id.exists' => 'The specified measure does not exist.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
