<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules =  [

            'name' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:10|max:2000',
            'project_url' => ['nullable','string','max:255','regex:/^https?:\/\/.+$/i'],
            'start_date' => 'required',
            'end_date'   => 'required|after_or_equal:start_date',
            'progress' => 'required|numeric|min:0|max:100',
            'comments' => 'nullable|string|max:1000',
            'budget' => 'required|numeric|gte:0',
            'weight' => 'required|numeric|between:0,1',

            // INDICATORS
            'indicators' => 'required|array|min:1',
            'indicators.*.id' => 'required|integer|exists:indicator,id',
            'indicators.*.name' => 'required|string|min:2|max:100',

            // DONORS
            'donors' => 'required|array|min:1',
            'donors.*.id' => 'required|integer|exists:donor,id',
            'donors.*.name' => 'required|string|min:2|max:150',
            'donors.*.contribution' => 'required|numeric|min:0',

            // AGENCIES
            'agencies' => 'required|array|min:1',
            'agencies.*.id' => 'required|integer|exists:agency,id',
            'agencies.*.name' => 'required|string|min:2|max:150',
            'agencies.*.contribution' => 'required|numeric|min:0',

            'contact.first_name' => 'required|string|min:2|max:50|regex:/^[a-zA-ZÀ-ÿñÑ\s\'\-\.,\/]+$/u',
            'contact.last_name'  => 'required|string|min:2|max:50|regex:/^[a-zA-ZÀ-ÿñÑ\s\'\-\.,\/]+$/u',
            'contact.title'      => 'required|string|min:2|max:100|regex:/^[a-zA-ZÀ-ÿñÑ\s\'\-\.,\/]+$/u',
            'contact.email'      => 'required|email|max:254',
            'contact.phone'      => 'nullable|string|regex:/^(\+?\d{1,4})?[\s\-]?\(?\d{1,4}\)?[\s\-]?\d+(\s?\-?\d+)*$/',

            'beneficiary.id'   => 'required|integer|exists:beneficiary,id',
            'beneficiary.name' => 'required|string',

            'project_state.id'    => 'required|integer|exists:project_state,id',
            'project_state.state' => 'required|string|min:3|max:100',

            'program_id' => 'required|numeric'
            
        ];

        if ($this->isMethod('put')) $rules['contact.id'] = 'required|integer|exists:contact,id';
        
        return $rules;
    }

    public function messages(): array
    {
        return [

           // PROJECT

            'name.required' => 'The project name is required.',
            'name.string' => 'The project name must be a valid text.',
            'name.min' => 'The project name must be at least 3 characters long.',
            'name.max' => 'The project name may not exceed 255 characters.',

            'description.required' => 'The project description is required.',
            'description.string' => 'The project description must be a valid text.',
            'description.min' => 'The project description must be at least 10 characters long.',
            'description.max' => 'The project description may not exceed 2000 characters.',

            'project_url.string' => 'The project URL must be a valid text.',
            'project_url.max' => 'The project URL may not exceed 255 characters.',
            'project_url.regex' => 'The project URL must start with http:// or https://.',

            'start_date.required' => 'The start date is required.',
            'start_date.date_format' => 'The start date must have the format Y-m-d.',

            'end_date.required' => 'The end date is required.',
            'end_date.date_format' => 'The end date must have the format Y-m-d.',
            'end_date.after_or_equal' => 'The end date must be equal to or later than the start date.',

            'progress.required' => 'The project progress is required.',
            'progress.numeric' => 'The project progress must be a numeric value.',
            'progress.min' => 'The project progress cannot be less than 0%.',
            'progress.max' => 'The project progress cannot exceed 100%.',

            'comments.string' => 'The comments must be valid text.',
            'comments.max' => 'The comments may not exceed 1000 characters.',

            'budget.required' => 'The project budget is required.',
            'budget.numeric' => 'The project budget must be a valid number.',
            'budget.gt' => 'The project budget must be greater than 0.',

            'weight.required' => 'The project weight is required.',
            'weight.numeric' => 'The project weight must be a number.',
            'weight.between' => 'The project weight must be between 0 and 1.',

            // CONTACT
            'contact.first_name.required' => 'The contact first name is required.',
            'contact.first_name.string'   => 'The contact first name must be valid text.',
            'contact.first_name.min'      => 'The contact first name must be at least 2 characters long.',
            'contact.first_name.max'      => 'The contact first name may not exceed 50 characters.',
            'contact.first_name.regex'    => 'The contact first name contains invalid characters.',

            'contact.last_name.required' => 'The contact last name is required.',
            'contact.last_name.string'   => 'The contact last name must be valid text.',
            'contact.last_name.min'      => 'The contact last name must be at least 2 characters long.',
            'contact.last_name.max'      => 'The contact last name may not exceed 50 characters.',
            'contact.last_name.regex'    => 'The contact last name contains invalid characters.',

            'contact.title.required' => 'The contact title is required.',
            'contact.title.string'   => 'The contact title must be valid text.',
            'contact.title.min'      => 'The contact title must be at least 2 characters long.',
            'contact.title.max'      => 'The contact title may not exceed 100 characters.',
            'contact.title.regex'    => 'The contact title contains invalid characters.',

            'contact.email.required' => 'The contact email is required.',
            'contact.email.email'    => 'The contact email is not valid.',
            'contact.email.max'      => 'The contact email may not exceed 254 characters.',

            'contact.phone.string' => 'The phone number must be valid text.',
            'contact.phone.regex'  => 'The provided phone number is not valid.',

            // BENEFICIARY
            'beneficiary.id.required' => 'The beneficiary is required.',
            'beneficiary.id.integer'  => 'The beneficiary ID must be a valid number.',
            'beneficiary.id.exists'   => 'The selected beneficiary does not exist.',

            'beneficiary.name.required' => 'The beneficiary name is required.',
            'beneficiary.name.string'   => 'The beneficiary name must be valid text.',

            // PROJECT STATE
            'project_state.id.required' => 'The project state is required.',
            'project_state.id.integer'  => 'The project state ID must be a valid number.',
            'project_state.id.exists'   => 'The selected project state does not exist.',

            'project_state.state.required' => 'The project state name is required.',
            'project_state.state.string'   => 'The project state name must be valid text.',
            'project_state.state.min'      => 'The project state name must be at least 3 characters long.',
            'project_state.state.max'      => 'The project state name may not exceed 100 characters.',

            // CONTACT (update)
            'contact.id.required' => 'The contact ID is required for update.',
            'contact.id.integer'  => 'The contact ID must be a valid number.',
            'contact.id.exists'   => 'The selected contact does not exist.',

            // INDICATORS
            'indicators.required' => 'At least one indicator is required.',
            'indicators.array' => 'Indicators must be sent as a list.',
            'indicators.min' => 'At least one indicator must be selected.',

            'indicators.*.id.required' => 'The indicator ID is required.',
            'indicators.*.id.integer' => 'The indicator ID must be a valid number.',
            'indicators.*.id.exists' => 'The selected indicator does not exist.',

            'indicators.*.name.required' => 'The indicator name is required.',
            'indicators.*.name.string' => 'The indicator name must be a valid text.',
            'indicators.*.name.min' => 'The indicator name must have at least 2 characters.',
            'indicators.*.name.max' => 'The indicator name may not exceed 100 characters.',

            // DONORS
            'donors.required' => 'At least one donor is required.',
            'donors.array' => 'Donors must be sent as a list.',
            'donors.min' => 'At least one donor must be provided.',

            'donors.*.id.required' => 'The donor ID is required.',
            'donors.*.id.integer' => 'The donor ID must be a valid number.',
            'donors.*.id.exists' => 'The selected donor does not exist.',

            'donors.*.name.required' => 'The donor name is required.',
            'donors.*.name.string' => 'The donor name must be a valid text.',
            'donors.*.name.min' => 'The donor name must have at least 2 characters.',
            'donors.*.name.max' => 'The donor name may not exceed 150 characters.',

            'donors.*.contribution.required' => 'The donor contribution amount is required.',
            'donors.*.contribution.numeric' => 'The donor contribution must be a number.',
            'donors.*.contribution.min' => 'The donor contribution cannot be negative.',

            // AGENCIES
            'agencies.required' => 'At least one agency is required.',
            'agencies.array' => 'Agencies must be sent as a list.',
            'agencies.min' => 'At least one agency must be provided.',

            'agencies.*.id.required' => 'The agency ID is required.',
            'agencies.*.id.integer' => 'The agency ID must be a valid number.',
            'agencies.*.id.exists' => 'The selected agency does not exist.',

            'agencies.*.name.required' => 'The agency name is required.',
            'agencies.*.name.string' => 'The agency name must be a valid text.',
            'agencies.*.name.min' => 'The agency name must have at least 2 characters.',
            'agencies.*.name.max' => 'The agency name may not exceed 150 characters.',

            'agencies.*.contribution.required' => 'The agency contribution amount is required.',
            'agencies.*.contribution.numeric' => 'The agency contribution must be a number.',
            'agencies.*.contribution.min' => 'The agency contribution cannot be negative.',

        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $programId = (int) $this->input('program_id');
            $weight = (float) $this->input('weight');

            if ($programId <= 0) {
                return;
            }

            $excludeProjectId = $this->isMethod('put') ? (int) $this->route('id') : null;

            $sumQuery = DB::table('project')->where('program_id', $programId);
            if (!empty($excludeProjectId)) {
                $sumQuery->where('id', '!=', $excludeProjectId);
            }

            $currentSum = (float) $sumQuery->sum('weight');
            $newTotal = round($currentSum + $weight, 4);

            if ($newTotal > 1) {
                $validator->errors()->add('weight', 'The sum of project weights for this program cannot exceed 1.');
            }
        });
    }


    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
