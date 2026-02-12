<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            // COUNTRY
            'country.id'   => 'nullable|integer|exists:country,id',
            'country.name' => 'nullable|string|min:2|max:150|required_with:country.id',

            // KPA
            'kpa.id'   => 'nullable|integer|exists:kpa,id',
            'kpa.name' => 'nullable|string|min:2|max:150|required_with:kpa.id',

            // STRATEGIC OUTPUT
            'strategic_output.id'   => 'nullable|integer|exists:strategic_output,id',
            'strategic_output.name' => 'nullable|string|min:2|max:150|required_with:strategic_output.id',

            // MEASURE
            'measure.id'   => 'nullable|integer|exists:measure,id',
            'measure.name' => 'nullable|string|min:2|max:150|required_with:measure.id',

            // PROJECT STATE
            'project_state.id'   => 'nullable|integer|exists:project_state,id',
            'project_state.state' => 'nullable|string|min:2|max:100|required_with:project_state.id',
        ];
    }

    public function messages(): array
    {
        return [

            // COUNTRY
            'country.id.integer' => 'The country ID must be a valid number.',
            'country.id.exists'  => 'The selected country does not exist.',
            'country.name.required_with' => 'Country name is required when country ID is provided.',

            // KPA
            'kpa.id.integer' => 'The KPA ID must be a valid number.',
            'kpa.id.exists'  => 'The selected KPA does not exist.',
            'kpa.id.required_with' => 'Country is required when filtering by KPA.',
            'kpa.name.required_with' => 'KPA name is required when KPA ID is provided.',

            // STRATEGIC OUTPUT
            'strategic_output.id.integer' => 'The strategic output ID must be a valid number.',
            'strategic_output.id.exists'  => 'The selected strategic output does not exist.',
            'strategic_output.id.required_with' => 'KPA is required when filtering by strategic output.',
            'strategic_output.name.required_with' => 'Strategic output name is required when strategic output ID is provided.',

            // MEASURE
            'measure.id.integer' => 'The measure ID must be a valid number.',
            'measure.id.exists'  => 'The selected measure does not exist.',
            'measure.id.required_with' => 'Strategic output is required when filtering by measure.',
            'measure.name.required_with' => 'Measure name is required when measure ID is provided.',

            // PROJECT STATE
            'project_state.id.integer' => 'The project state ID must be a valid number.',
            'project_state.id.exists'  => 'The selected project state does not exist.',
            'project_state.state.required_with' => 'Project state name is required when project state ID is provided.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
