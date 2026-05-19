<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\ValidatesNestedContact;
use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ProgramRequest extends FormRequest
{
    use ValidatesNestedContact;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $programId = $this->route('id');

        return array_merge([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('program', 'name')->ignore($programId)
            ],
            'description' => 'required|string|max:2000',
            'banner_img' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'program_url' => 'nullable|string|url|regex:/^https?:\/\//',
            'program_state_id' => $this->isMethod('PUT')
                ? 'required|integer|min:1|exists:program_state,id'
                : 'nullable|integer|min:1|exists:program_state,id',
            'sdg_ids' => 'required|array|min:1',
            'sdg_ids.*' => 'integer|min:1|exists:sdg,id',
            'country_id' => 'nullable|integer|exists:country,id',
        ], $this->contactRules());
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge([
            'name.required' => 'The program name is required.',
            'name.string' => 'The name must be a text string.',
            'name.max' => 'The program name must not exceed 255 characters.',
            'name.unique' => 'A program with this name already exists.',
            'description.required' => 'The program description is required.',
            'description.max' => 'The description must not exceed 2000 characters.',
            'banner_img.image' => 'The file must be an image.',
            'banner_img.mimes' => 'The image must be of type: jpg, jpeg, png, gif or webp.',
            'banner_img.max' => 'The image must not exceed 2MB.',
            'program_url.url' => 'The program URL must be valid.',
            'program_url.regex' => 'The program URL must use HTTP or HTTPS protocol.',
            'program_state_id.required' => 'The state ID is required (only on update).',
            'program_state_id.exists' => 'The selected state does not exist.',
            'sdg_ids.required' => 'You must select at least one SDG.',
            'sdg_ids.array' => 'The SDGs must be an array.',
            'sdg_ids.min' => 'You must select at least one SDG.',
            'sdg_ids.*.exists' => 'One or more selected SDGs do not exist.',
            'country_id.integer' => 'The country ID must be an integer.',
            'country_id.exists' => 'The selected country does not exist.',
        ], $this->contactMessages());
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
