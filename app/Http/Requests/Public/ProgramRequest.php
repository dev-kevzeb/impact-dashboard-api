<?php

namespace App\Http\Requests\Public;

use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // COUNTRY
            'country.id' => 'nullable|integer|exists:country,id',
            'country.name' => 'nullable|string|min:2|max:150|required_with:country.id',

            // KPA
            'kpa.id' => 'nullable|integer|exists:kpa,id',
            'kpa.name' => 'nullable|string|min:2|max:150|required_with:kpa.id',

            // STRATEGIC OUTPUT
            'strategic_output.id' => 'nullable|integer|exists:strategic_output,id',
            'strategic_output.name' => 'nullable|string|min:2|max:150|required_with:strategic_output.id',

            // MEASURE
            'measure.id' => 'nullable|integer|exists:measure,id',
            'measure.name' => 'nullable|string|min:2|max:150|required_with:measure.id',

            // PROGRAM STATE
            'program_state.id' => 'nullable|integer|exists:program_state,id',
            'program_state.name' => 'nullable|string|min:2|max:150|required_with:program_state.id',
        ];
    }

    public function messages(): array
    {
        return [
            // COUNTRY
            'country.id.integer' => 'The country ID must be a valid number.',
            'country.id.exists' => 'The selected country does not exist.',
            'country.name.required_with' => 'Country name is required when country ID is provided.',

            // KPA
            'kpa.id.integer' => 'The KPA ID must be a valid number.',
            'kpa.id.exists' => 'The selected KPA does not exist.',
            'kpa.name.required_with' => 'KPA name is required when KPA ID is provided.',

            // STRATEGIC OUTPUT
            'strategic_output.id.integer' => 'The strategic output ID must be a valid number.',
            'strategic_output.id.exists' => 'The selected strategic output does not exist.',
            'strategic_output.name.required_with' => 'Strategic output name is required when strategic output ID is provided.',

            // MEASURE
            'measure.id.integer' => 'The measure ID must be a valid number.',
            'measure.id.exists' => 'The selected measure does not exist.',
            'measure.name.required_with' => 'Measure name is required when measure ID is provided.',

            // PROGRAM STATE
            'program_state.id.integer' => 'The program state ID must be a valid number.',
            'program_state.id.exists' => 'The selected program state does not exist.',
            'program_state.name.required_with' => 'Program state name is required when program state ID is provided.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
