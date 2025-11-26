<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ProgramRequest extends FormRequest
{
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
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('program', 'name')->ignore($programId)
            ],
            'description' => 'required|string|max:2000',
            'banner_img' => $this->isMethod('PUT') 
                ? 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048'
                : 'required|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'start_date' => 'required|date_format:Y-m-d|after_or_equal:-10 years',
            'end_date' => 'required|date_format:Y-m-d|after:start_date|before_or_equal:start_date +20 years',
            'program_url' => 'nullable|string|url|regex:/^https?:\/\//',
            'contact_id' => 'required|integer|min:1|exists:contact,id',
            'beneficiary_id' => 'required|integer|min:1|exists:beneficiary,id',
            'program_state_id' => 'required|integer|min:1|exists:program_state,id',
            'country_id' => 'required|integer|min:1|exists:country,id',
            'agency_id' => 'required|integer|min:1|exists:agency,id',
            'sdg_ids' => 'nullable|array',
            'sdg_ids.*' => 'integer|min:1|exists:sdg,id',
            'donor_ids' => 'nullable|array',
            'donor_ids.*' => 'integer|min:1|exists:donor,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del programa es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre del programa no debe exceder 255 caracteres.',
            'name.unique' => 'Ya existe un programa con este nombre.',
            'description.required' => 'La descripción del programa es obligatoria.',
            'description.max' => 'La descripción no debe exceder 2000 caracteres.',
            'banner_img.required' => 'La imagen banner es obligatoria.',
            'banner_img.image' => 'El archivo debe ser una imagen.',
            'banner_img.mimes' => 'La imagen debe ser de tipo: jpg, jpeg, png, gif o webp.',
            'banner_img.max' => 'La imagen no debe exceder 2MB.',
            'start_date.required' => 'La fecha de inicio es obligatoria.',
            'start_date.date_format' => 'La fecha de inicio debe tener formato YYYY-MM-DD.',
            'start_date.after_or_equal' => 'La fecha de inicio no puede ser anterior a 10 años.',
            'end_date.required' => 'La fecha de fin es obligatoria.',
            'end_date.date_format' => 'La fecha de fin debe tener formato YYYY-MM-DD.',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'end_date.before_or_equal' => 'La duración del programa no puede exceder 20 años.',
            'program_url.url' => 'La URL del programa debe ser válida.',
            'program_url.regex' => 'La URL del programa debe usar protocolo HTTP o HTTPS.',
            'contact_id.required' => 'El ID del contacto es obligatorio.',
            'contact_id.exists' => 'El contacto seleccionado no existe.',
            'beneficiary_id.required' => 'El ID del beneficiario es obligatorio.',
            'beneficiary_id.exists' => 'El beneficiario seleccionado no existe.',
            'program_state_id.required' => 'El ID del estado es obligatorio.',
            'program_state_id.exists' => 'El estado seleccionado no existe.',
            'country_id.required' => 'El ID del país es obligatorio.',
            'country_id.exists' => 'El país seleccionado no existe.',
            'agency_id.required' => 'El ID de la agencia es obligatorio.',
            'agency_id.exists' => 'La agencia seleccionada no existe.',
            'sdg_ids.array' => 'Los SDGs deben ser un array.',
            'sdg_ids.*.exists' => 'Uno o más SDGs seleccionados no existen.',
            'donor_ids.array' => 'Los donantes deben ser un array.',
            'donor_ids.*.exists' => 'Uno o más donantes seleccionados no existen.',
        ];
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
