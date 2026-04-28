<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class BeneficiaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $beneficiaryId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('beneficiary', 'name')->ignore($beneficiaryId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Beneficiary name is required.',
            'name.string'   => 'The name must be a text string.',
            'name.min'      => 'Beneficiary name must be at least 2 characters long.',
            'name.max'      => 'Name must not exceed 255 characters.',
            'name.unique'   => 'This beneficiary already exists in the system.',
        ];
    }


    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
