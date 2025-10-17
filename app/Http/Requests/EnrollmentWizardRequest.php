<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnrollmentWizardRequest extends FormRequest
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
        return [
            'step' => 'required|string|in:matricula',
            'student_id' => 'required|exists:students,id',
            'guardian_id' => 'required|exists:guardians,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'status' => 'required|in:active,pending,cancelled,inactive',
            'process' => 'required|in:reserva,aguardando_pagamento,aguardando_documentos,desistencia,transferencia,renovacao,completa',
            'enrollment_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'step.required' => 'O step é obrigatório.',
            'step.in' => 'Step inválido.',
            
            'student_id.required' => 'O aluno é obrigatório.',
            'student_id.exists' => 'O aluno selecionado não existe.',
            
            'guardian_id.required' => 'O responsável é obrigatório.',
            'guardian_id.exists' => 'O responsável selecionado não existe.',
            
            'classroom_id.required' => 'A turma é obrigatória.',
            'classroom_id.exists' => 'A turma selecionada não existe.',
            
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'Status inválido.',
            
            'process.required' => 'O processo é obrigatório.',
            'process.in' => 'Processo inválido.',
            
            'enrollment_date.required' => 'A data da matrícula é obrigatória.',
            'enrollment_date.date' => 'Data inválida.',
            'enrollment_date.before_or_equal' => 'A data da matrícula não pode ser futura.',
            
            'notes.max' => 'As observações não podem ter mais de 1000 caracteres.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar se há vagas na turma
            if ($this->classroom_id) {
                $classroom = \App\Models\Classroom::find($this->classroom_id);
                if ($classroom) {
                    $enrolledCount = \App\Models\Enrollment::where('classroom_id', $classroom->id)
                        ->where('status', '!=', 'cancelled')
                        ->count();
                    
                    if ($enrolledCount >= $classroom->vacancies) {
                        $validator->errors()->add('classroom_id', 'Esta turma não possui vagas disponíveis.');
                    }
                }
            }

            // Validar se o aluno já está matriculado na turma
            if ($this->student_id && $this->classroom_id) {
                $exists = \App\Models\Enrollment::where('student_id', $this->student_id)
                    ->where('classroom_id', $this->classroom_id)
                    ->where('status', '!=', 'cancelled')
                    ->exists();
                
                if ($exists) {
                    $validator->errors()->add('student_id', 'Este aluno já está matriculado nesta turma.');
                }
            }
        });
    }
}
