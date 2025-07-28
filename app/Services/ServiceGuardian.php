<?php

namespace App\Services;

use App\Models\Guardian;
use App\Models\Enrollment;
use App\Models\Student;

class ServiceGuardian
{
    public function create(array $data)
    {
        // Separar email e phone dos dados principais
        $email = $data['email'] ?? null;
        $phone = $data['phone'] ?? null;
        
        // Remover email e phone dos dados do guardian
        $guardianData = collect($data)->except(['email', 'phone', 'addresses', 'contacts'])->toArray();
        
        $guardian = Guardian::create($guardianData);
        
        // Criar contatos básicos a partir dos dados principais (email e phone)
        if ($email) {
            $guardian->contacts()->create([
                'type' => 'email',
                'value' => $email,
                'label' => 'pessoal',
                'is_primary' => true,
                'contact_for' => 'pessoal',
            ]);
        }
        
        if ($phone) {
            $guardian->contacts()->create([
                'type' => 'phone',
                'value' => $phone,
                'label' => 'pessoal',
                'is_primary' => true,
                'contact_for' => 'pessoal',
            ]);
        }
        
        // Endereços
        if (!empty($data['addresses']) && is_array($data['addresses'])) {
            foreach ($data['addresses'] as $address) {
                if (!empty(array_filter($address))) {
                    // Ensure required fields are present
                    $addressData = array_merge([
                        'type' => 'residencial', // Default type if not provided
                    ], $address);
                    $guardian->addresses()->create($addressData);
                }
            }
        }

        // Contatos adicionais (se fornecidos)
        if (!empty($data['contacts']) && is_array($data['contacts'])) {
            foreach ($data['contacts'] as $contact) {
                if (!empty(array_filter($contact))) {
                    $guardian->contacts()->create($contact);
                }
            }
        }

        return $guardian;
    }

    public function update(Guardian $guardian, array $data): Guardian
    {
        $guardian->update($data);
        return $guardian;
    }

    public function delete(Guardian $guardian): bool
    {
        return $guardian->delete();
    }

    public function find($id): ?Guardian
    {
        return Guardian::find($id);
    }

    public function search($term = null)
    {
        return Guardian::query()
            ->when($term, fn($q) => $q->where('name', 'like', "%$term%"))
            ->limit(10)
            ->get();
    }

    /**
     * Vincular responsável a um aluno
     */
    public function attachToStudent($guardianId, $studentId)
    {
        $guardian = Guardian::findOrFail($guardianId);
        $student = Student::findOrFail($studentId);
        
        // Verificar se já está vinculado
        if ($guardian->students()->where('student_id', $studentId)->exists()) {
            return $guardian; // Já vinculado, não faz nada
        }
        
        $guardian->students()->attach($studentId, [
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return $guardian;
    }

    /**
     * Desvincular responsável de um aluno
     */
    public function detachFromStudent($guardianId, $studentId)
    {
        $guardian = Guardian::findOrFail($guardianId);
        $guardian->students()->detach($studentId);
        return $guardian;
    }

    /**
     * Buscar responsáveis não vinculados a um aluno específico
     */
    public function searchNotLinkedToStudent($studentId, $term = null)
    {
        return Guardian::query()
            ->whereDoesntHave('students', function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            })
            ->when($term, fn($q) => $q->where('name', 'like', "%$term%"))
            ->limit(10)
            ->get();
    }
} 