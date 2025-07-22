<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'cpf' => 'nullable|string|max:20',
            'rg' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'birth_certificate_number' => 'nullable|string|max:50',
            'grade' => 'nullable|string|max:50',
            'shift' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'contacts' => 'nullable|array',
            'photo' => 'nullable|image|max:2048',
        ];
    }
} 