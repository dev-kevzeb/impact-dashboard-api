<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class KpaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $kpaId = $this->route("id");

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:300',
                Rule::unique('kpa', 'name')->ignore($kpaId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // NAME
            'name.required' => 'The KPA name must not be empty',
            'name.min'      => 'The KPA name must be at least 2 characters long',
            'name.max'      => 'The KPA name must not exceed 300 characters',
            'name.string'   => 'The KPA name must be a text string',
            'name.unique'   => 'A KPA with this name already exists',
        ];

    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
