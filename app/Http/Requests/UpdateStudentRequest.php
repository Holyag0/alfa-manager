<?php
// app/Http/Requests/UpdateStudentRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $student = $this->route('student');

        return [
            'name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today',
            'cpf' => [
                'nullable',
                'string',
                'size:11',
                Rule::unique('students', 'cpf')->ignore($student->id),
            ],
            'rg' => 'nullable|string|max:20',
            'gender' => 'nullable|in:M,F,other',
            'phone' => 'nullable|string|max:15',
            'email' => [
                'nullable',
                'email',
                Rule::unique('students', 'email')->ignore($student->id),
            ],
            
            // Endereço
            'address' => 'required|array',
            'address.street' => 'required|string|max:255',
            'address.number' => 'required|string|max:10',
            'address.complement' => 'nullable|string|max:100',
            'address.district' => 'required|string|max:100',
            'address.city' => 'required|string|max:100',
            'address.state' => 'required|string|size:2',
            'address.zipcode' => 'required|string|max:10',
            
            // Foto
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            
            // Status
            'status' => 'required|in:active,inactive,transferred,graduated',
            'notes' => 'nullable|string|max:1000',
            
            // Turma (opcional na edição)
            'classroom_id' => 'nullable|exists:classrooms,id',
            
            // Responsáveis
            'guardians' => 'required|array|min:1|max:4',
            'guardians.*.id' => 'required|exists:guardians,id',
            'guardians.*.relationship' => 'required|in:father,mother,grandfather,grandmother,uncle,aunt,brother,sister,other',
            'guardians.*.is_primary' => 'boolean',
            'guardians.*.is_financial' => 'boolean',
            'guardians.*.can_pickup' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do aluno é obrigatório.',
            'birth_date.required' => 'A data de nascimento é obrigatória.',
            'cpf.unique' => 'Este CPF já está cadastrado para outro aluno.',
            'email.unique' => 'Este email já está cadastrado para outro aluno.',
            'status.required' => 'O status do aluno é obrigatório.',
            'status.in' => 'Status inválido.',
            'guardians.required' => 'É necessário pelo menos um responsável.',
        ];
    }

    protected function prepareForValidation()
    {
        // Limpar CPF e telefone
        if ($this->cpf) {
            $this->merge(['cpf' => preg_replace('/\D/', '', $this->cpf)]);
        }

        if ($this->phone) {
            $this->merge(['phone' => preg_replace('/\D/', '', $this->phone)]);
        }

        // Garantir responsável principal
        if ($this->guardians) {
            $hasPrimary = collect($this->guardians)->contains('is_primary', true);
            
            if (!$hasPrimary && count($this->guardians) > 0) {
                $guardians = $this->guardians;
                $guardians[0]['is_primary'] = true;
                $this->merge(['guardians' => $guardians]);
            }
        }
    }
}