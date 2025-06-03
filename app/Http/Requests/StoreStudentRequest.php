<?php
// app/Http/Requests/StoreStudentRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ou verificar permissões
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today|after:' . now()->subYears(18)->toDateString(),
            'cpf' => 'nullable|string|size:11|unique:students,cpf',
            'rg' => 'nullable|string|max:20',
            'gender' => 'nullable|in:M,F,other',
            'phone' => 'nullable|string|max:15',
            'email' => 'nullable|email|unique:students,email',
            
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
            
            // Matrícula
            'enrollment_date' => 'required|date|before_or_equal:today',
            'classroom_id' => 'required|exists:classrooms,id',
            'notes' => 'nullable|string|max:1000',
            
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
            'birth_date.before' => 'A data de nascimento deve ser anterior a hoje.',
            'birth_date.after' => 'O aluno deve ter menos de 18 anos.',
            'cpf.size' => 'O CPF deve ter exatamente 11 dígitos.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'email.unique' => 'Este email já está cadastrado.',
            'classroom_id.required' => 'Selecione uma turma para o aluno.',
            'classroom_id.exists' => 'A turma selecionada não existe.',
            'guardians.required' => 'É necessário cadastrar pelo menos um responsável.',
            'guardians.min' => 'É necessário cadastrar pelo menos um responsável.',
            'guardians.max' => 'Máximo de 4 responsáveis permitidos.',
            'guardians.*.id.exists' => 'Um dos responsáveis selecionados não existe.',
            'guardians.*.relationship.required' => 'O parentesco é obrigatório.',
            'guardians.*.relationship.in' => 'Parentesco inválido.',
            'address.street.required' => 'O endereço é obrigatório.',
            'address.district.required' => 'O bairro é obrigatório.',
            'address.city.required' => 'A cidade é obrigatória.',
            'address.state.required' => 'O estado é obrigatório.',
            'address.state.size' => 'O estado deve ter 2 caracteres (ex: CE).',
            'photo.image' => 'O arquivo deve ser uma imagem.',
            'photo.mimes' => 'A foto deve ser nos formatos: jpeg, png ou jpg.',
            'photo.max' => 'A foto deve ter no máximo 2MB.',
        ];
    }

    protected function prepareForValidation()
    {
        // Limpar CPF (remover pontos e traços)
        if ($this->cpf) {
            $this->merge([
                'cpf' => preg_replace('/\D/', '', $this->cpf)
            ]);
        }

        // Limpar telefone
        if ($this->phone) {
            $this->merge([
                'phone' => preg_replace('/\D/', '', $this->phone)
            ]);
        }

        // Garantir que pelo menos um responsável seja principal
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
