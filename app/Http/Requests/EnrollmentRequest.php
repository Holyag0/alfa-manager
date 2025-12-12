<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnrollmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'student_id' => 'nullable|exists:students,id',
            'guardian_id' => 'nullable|exists:guardians,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'academic_year' => 'nullable|integer|min:2000|max:' . (now()->year + 5),
            'status' => 'nullable|in:active,pending,cancelled,inactive',
            'process' => 'nullable|in:reserva,aguardando_pagamento,aguardando_documentos,desistencia,transferencia,renovacao,completa',
            'enrollment_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }
} 