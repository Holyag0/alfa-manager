<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WizardRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $step = $this->input('step');
        
        switch ($step) {
            case 'aluno':
                return [
                    'student.name' => 'required|string|max:255',
                    'student.birth_date' => 'required|date',
                    'student.cpf' => 'required|string|size:11',
                    'student.rg' => 'nullable|string|max:20',
                    'student.birth_certificate_number' => 'nullable|string|max:50',
                    'student.notes' => 'nullable|string',
                ];
                
            case 'responsavel':
                return [
                    'guardian.name' => 'required|string|max:255',
                    'guardian.cpf' => 'required|string|size:11',
                    'guardian.email' => 'required|email|max:255',
                    'guardian.phone' => 'nullable|string|max:20',
                    'guardian.guardian_type' => 'required|in:titular,suplente',
                    'guardian.relationship' => 'required|string|max:50',
                    'guardian.occupation' => 'nullable|string|max:100',
                    'guardian.workplace' => 'nullable|string|max:100',
                    'guardian.addresses' => 'nullable|array',
                    'guardian.addresses.*.type' => 'nullable|string|max:50',
                    'guardian.addresses.*.zip_code' => 'nullable|string|max:10',
                    'guardian.addresses.*.street' => 'nullable|string|max:255',
                    'guardian.addresses.*.number' => 'nullable|string|max:20',
                    'guardian.addresses.*.complement' => 'nullable|string|max:100',
                    'guardian.addresses.*.neighborhood' => 'nullable|string|max:100',
                    'guardian.addresses.*.city' => 'nullable|string|max:100',
                    'guardian.addresses.*.state' => 'nullable|string|max:2',
                    'guardian.addresses.*.address_for' => 'nullable|string|max:50',
                    'guardian.addresses.*.is_primary' => 'nullable|boolean',
                    'guardian.contacts' => 'nullable|array',
                    'guardian.contacts.*.type' => 'nullable|string|max:20',
                    'guardian.contacts.*.value' => 'nullable|string|max:255',
                    'guardian.contacts.*.label' => 'nullable|string|max:50',
                    'guardian.contacts.*.is_primary' => 'nullable|boolean',
                ];
                
            case 'matricula':
                return [
                    'enrollment.classroom_id' => 'required|exists:classrooms,id',
                    'enrollment.enrollment_date' => 'required|date',
                    'enrollment.status' => 'nullable|in:active,pending,cancelled,inactive',
                    'enrollment.process' => 'nullable|in:reserva,aguardando_pagamento,aguardando_documentos,desistencia,transferencia,renovacao,completa',
                    'enrollment.notes' => 'nullable|string',
                ];
                
            default:
                return [];
        }
    }
    
    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'student.name.required' => 'O nome do aluno é obrigatório.',
            'student.birth_date.required' => 'A data de nascimento é obrigatória.',
            'student.cpf.required' => 'O CPF do aluno é obrigatório.',
            'student.cpf.size' => 'O CPF deve ter 11 dígitos.',
            
            'guardian.name.required' => 'O nome do responsável é obrigatório.',
            'guardian.cpf.required' => 'O CPF do responsável é obrigatório.',
            'guardian.cpf.size' => 'O CPF deve ter 11 dígitos.',
            'guardian.email.required' => 'O email do responsável é obrigatório.',
            'guardian.email.email' => 'O email deve ter um formato válido.',
            'guardian.guardian_type.required' => 'O tipo de responsável é obrigatório.',
            'guardian.relationship.required' => 'O parentesco é obrigatório.',
            
            'enrollment.classroom_id.required' => 'A turma é obrigatória.',
            'enrollment.classroom_id.exists' => 'A turma selecionada não existe.',
            'enrollment.enrollment_date.required' => 'A data da matrícula é obrigatória.',
        ];
    }
}
