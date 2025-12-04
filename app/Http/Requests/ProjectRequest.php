<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            'start_date' => 'required|date_format:Y-m-d',
            'end_date'   => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'progress' => 'required|numeric|min:0|max:100',
            'comments' => 'nullable|string|max:1000',
            'project_budget' => 'required|numeric|gt:0',

            'contact.first_name' => 'required|string|min:2|max:50|regex:/^[a-zA-ZÀ-ÿñÑ\s\'\-\.,\/]+$/u',
            'contact.last_name'  => 'required|string|min:2|max:50|regex:/^[a-zA-ZÀ-ÿñÑ\s\'\-\.,\/]+$/u',
            'contact.title'      => 'required|string|min:2|max:100|regex:/^[a-zA-ZÀ-ÿñÑ\s\'\-\.,\/]+$/u',
            'contact.email'      => 'required|email|max:254',
            'contact.phone'      => 'nullable|string|regex:/^(\+?\d{1,4})?[\s\-]?\(?\d{1,4}\)?[\s\-]?\d+(\s?\-?\d+)*$/',

            'beneficiary.id'   => 'required|integer|exists:beneficiary,id',
            'beneficiary.name' => 'required|string',

            'project_state.id'    => 'required|integer|exists:project_state,id',
            'project_state.state' => 'required|string|min:3|max:100',
            
        ];

        if ($this->isMethod('put')) $rules['contact.id'] = 'required|integer|exists:contact,id';
        
        return $rules;
    }

    public function messages(): array
    {
        return [

            // PROJECT
            'name.required' => 'El nombre del proyecto es obligatorio.',
            'name.string' => 'El nombre del proyecto debe ser un texto válido.',
            'name.min' => 'El nombre del proyecto debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre del proyecto no puede superar los 255 caracteres.',

            'description.required' => 'La descripción es obligatoria.',
            'description.string' => 'La descripción debe ser un texto válido.',
            'description.min' => 'La descripción debe tener al menos 10 caracteres.',
            'description.max' => 'La descripción no puede superar los 2000 caracteres.',

            'project_url.string' => 'La URL debe ser un texto válido.',
            'project_url.max' => 'La URL no puede superar los 255 caracteres.',
            'project_url.regex' => 'La URL del proyecto debe empezar con http:// o https://',


            'start_date.required' => 'La fecha de inicio es obligatoria.',
            'start_date.date_format' => 'La fecha de inicio debe tener el formato Y-m-d.',

            'end_date.required' => 'La fecha de finalización es obligatoria.',
            'end_date.date_format' => 'La fecha de finalización debe tener el formato Y-m-d.',
            'end_date.after_or_equal' => 'La fecha de finalización debe ser igual o posterior a la fecha de inicio.',

            'progress.required' => 'El progreso del proyecto es obligatorio.',
            'progress.numeric' => 'El progreso debe ser un valor numérico.',
            'progress.min' => 'El progreso no puede ser menor a 0%.',
            'progress.max' => 'El progreso no puede superar el 100%.',

            'comments.string' => 'Los comentarios deben ser texto válido.',
            'comments.max' => 'Los comentarios no pueden superar los 1000 caracteres.',

            'project_budget.required' => 'El presupuesto del proyecto es obligatorio.',
            'project_budget.numeric' => 'El presupuesto debe ser un número válido.',
            'project_budget.gt' => 'El presupuesto debe ser mayor a 0.',

            // CONTACT
            'contact.first_name.required' => 'El nombre del contacto es obligatorio.',
            'contact.first_name.string'   => 'El nombre del contacto debe ser un texto válido.',
            'contact.first_name.min'      => 'El nombre del contacto debe tener al menos 2 caracteres.',
            'contact.first_name.max'      => 'El nombre del contacto no puede superar los 50 caracteres.',
            'contact.first_name.regex'    => 'El nombre del contacto contiene caracteres no permitidos.',

            'contact.last_name.required' => 'El apellido del contacto es obligatorio.',
            'contact.last_name.string'   => 'El apellido del contacto debe ser un texto válido.',
            'contact.last_name.min'      => 'El apellido del contacto debe tener al menos 2 caracteres.',
            'contact.last_name.max'      => 'El apellido del contacto no puede superar los 50 caracteres.',
            'contact.last_name.regex'    => 'El apellido del contacto contiene caracteres no permitidos.',

            'contact.title.required' => 'El título del contacto es obligatorio.',
            'contact.title.string'   => 'El título del contacto debe ser un texto válido.',
            'contact.title.min'      => 'El título del contacto debe tener al menos 2 caracteres.',
            'contact.title.max'      => 'El título del contacto no puede superar los 100 caracteres.',
            'contact.title.regex'    => 'El título del contacto contiene caracteres no permitidos.',

            'contact.email.required' => 'El email del contacto es obligatorio.',
            'contact.email.email'    => 'El email del contacto no es válido.',
            'contact.email.max'      => 'El email del contacto no puede superar los 254 caracteres.',

            'contact.phone.string' => 'El número de teléfono debe ser un texto válido.',
            'contact.phone.regex'  => 'El número de teléfono proporcionado no es válido.',


            // BENEFICIARY
            'beneficiary.id.required' => 'El beneficiario es obligatorio.',
            'beneficiary.id.integer'  => 'El ID del beneficiario debe ser un número válido.',
            'beneficiary.id.exists'   => 'El beneficiario seleccionado no existe.',

            'beneficiary.name.required' => 'El nombre del beneficiario es obligatorio.',
            'beneficiary.name.string'   => 'El nombre del beneficiario debe ser texto válido.',


            // PROJECT STATE
            'project_state.id.required' => 'El estado del proyecto es obligatorio.',
            'project_state.id.integer'  => 'El ID del estado del proyecto debe ser un número válido.',
            'project_state.id.exists'   => 'El estado del proyecto seleccionado no existe.',

            'project_state.state.required' => 'El nombre del estado del proyecto es obligatorio.',
            'project_state.state.string'   => 'El nombre del estado debe ser texto válido.',
            'project_state.state.min'      => 'El nombre del estado debe tener al menos 3 caracteres.',
            'project_state.state.max'      => 'El nombre del estado no puede superar los 100 caracteres.',

            'contact.id.required' => 'El ID del contacto es obligatorio para actualizar.',
            'contact.id.integer'  => 'El ID del contacto debe ser un número válido.',
            'contact.id.exists'   => 'El contacto seleccionado no existe.',
        ];
    }


    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
