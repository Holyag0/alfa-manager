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
            'status' => 'nullable|string',
            'enrollment_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }
} 