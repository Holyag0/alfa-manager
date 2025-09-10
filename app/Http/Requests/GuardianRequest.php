<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:60',
            'cpf' => 'required|string|max:20', // CPF é obrigatório
            'rg' => 'nullable|string|max:20',
            'relationship' => 'nullable|string|max:50',
            'marital_status' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:20',
            'guardian_type' => 'nullable|string|max:50',
            'occupation' => 'nullable|string|max:100',
            'workplace' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            'addresses' => 'nullable|array',
            'addresses.*.zip_code' => 'nullable|string|max:10',
            'addresses.*.street' => 'nullable|string|max:255',
            'addresses.*.number' => 'nullable|string|max:50',
            'addresses.*.complement' => 'nullable|string|max:100',
            'addresses.*.neighborhood' => 'nullable|string|max:100',
            'addresses.*.city' => 'nullable|string|max:100',
            'addresses.*.state' => 'nullable|string|max:10',
            'addresses.*.address_for' => 'nullable|string|max:20',
            'addresses.*.is_primary' => 'nullable|boolean',
            'contacts' => 'required|array|min:1',
            'contacts.*.type' => 'required|string|max:20',
            'contacts.*.value' => 'required|string|max:100',
            'contacts.*.label' => 'nullable|string|max:30',
            'contacts.*.is_primary' => 'nullable|boolean',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Garantir que pelo menos um contato seja fornecido via contacts array
            $hasContacts = !empty($this->contacts) && is_array($this->contacts) && 
                          collect($this->contacts)->some(fn($c) => !empty($c['value']));
            
            if (!$hasContacts) {
                $validator->errors()->add('contacts', 'É necessário fornecer pelo menos um meio de contato (email ou telefone).');
            }
            
            // Validar se os contatos têm os campos obrigatórios
            if (!empty($this->contacts) && is_array($this->contacts)) {
                foreach ($this->contacts as $index => $contact) {
                    if (empty($contact['type'])) {
                        $validator->errors()->add("contacts.{$index}.type", 'O tipo do contato é obrigatório.');
                    }
                    if (empty($contact['value'])) {
                        $validator->errors()->add("contacts.{$index}.value", 'O valor do contato é obrigatório.');
                    }
                }
            }
        });
    }
} 