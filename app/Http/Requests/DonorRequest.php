<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class DonorRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        if ($this->has('name')) {
            $name = trim($this->input('name'));
            $name = preg_replace('/\s+/', ' ', $name);

            $this->merge([
                'name' => $name
            ]);
        }
    }
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Para update, excluir el registro actual de la validación unique
        $donorId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'regex:/^[\pL\s]+$/u',
                'max:255',
                Rule::unique('donor', 'name')->ignore($donorId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The donor name is required.',
            'name.string' => 'The name must be a string.',
            'name.regex' => 'The name must only contain letters and spaces.',
            'name.min' => 'The donor name must be at least 2 characters long.',
            'name.max' => 'The donor name must not exceed 255 characters.',
            'name.unique' => 'This donor already exists in the system.',
        ];

    }

    /**
     * Manejar validación fallida → JSON estándar
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
