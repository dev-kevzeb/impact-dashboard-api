<?php

namespace App\Http\Requests\Traits;

trait ValidatesNestedContact
{
    /**
     * Reglas de validación para contact anidado
     * Reutilizable en Program, Project
     */
    protected function contactRules(): array
    {
        return [
            'contact' => 'required|array',
            'contact.id' => 'nullable|integer|exists:contact,id',
            'contact.first_name' => [
                'required_without:contact.id',
                'string',
                'min:2',
                'max:50',
                'regex:/^[a-zA-ZÀ-ÿñÑ\s\'\-\.,\/]+$/u',
            ],
            'contact.last_name' => [
                'required_without:contact.id',
                'string',
                'min:2',
                'max:50',
                'regex:/^[a-zA-ZÀ-ÿñÑ\s\'\-\.,\/]+$/u',
            ],
            'contact.title' => [
                'required_without:contact.id',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-ZÀ-ÿñÑ\s\'\-\.,\/]+$/u',
            ],
            'contact.email' => [
                'required_without:contact.id',
                'string',
                'email',
                'max:254',
            ],
            'contact.phone' => [
                'nullable',
                'string',
                'regex:/^(\+?\d{1,4})?[\s\-]?\(?\d{1,4}\)?[\s\-]?\d+(\s?\-?\d+)*$/',
            ],
        ];
    }

    /**
     * Mensajes de validación para contact anidado
     */
    protected function contactMessages(): array
    {
        return [
            'contact.required' => 'Los datos del contacto son obligatorios.',
            'contact.array' => 'Los datos del contacto deben ser un objeto.',
            'contact.id.exists' => 'El contacto seleccionado no existe.',
            
            'contact.first_name.required_without' => 'El nombre del contacto es obligatorio.',
            'contact.first_name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'contact.first_name.max' => 'El nombre no debe exceder 50 caracteres.',
            'contact.first_name.regex' => 'El nombre del contacto contiene caracteres no válidos.',
            
            'contact.last_name.required_without' => 'El apellido del contacto es obligatorio.',
            'contact.last_name.min' => 'El apellido debe tener al menos 2 caracteres.',
            'contact.last_name.max' => 'El apellido no debe exceder 50 caracteres.',
            'contact.last_name.regex' => 'El apellido del contacto contiene caracteres no válidos.',
            
            'contact.title.required_without' => 'El título del contacto es obligatorio.',
            'contact.title.min' => 'El título debe tener al menos 2 caracteres.',
            'contact.title.max' => 'El título no debe exceder 100 caracteres.',
            'contact.title.regex' => 'El título del contacto contiene caracteres no válidos.',
            
            'contact.email.required_without' => 'El email del contacto es obligatorio.',
            'contact.email.email' => 'El email debe tener un formato válido.',
            'contact.email.max' => 'El email no debe exceder 254 caracteres.',
            
            'contact.phone.regex' => 'El formato del teléfono no es válido - use formato internacional.',
        ];
    }
}
