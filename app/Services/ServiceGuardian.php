<?php

namespace App\Services;

use App\Models\Guardian;
use App\Models\Enrollment;
use App\Models\Student;

class ServiceGuardian
{
    public function create(array $data)
    {
        try {
            // Remover addresses e contacts dos dados do guardian
            $guardianData = collect($data)->except(['addresses', 'contacts'])->toArray();
            
            $guardian = Guardian::create($guardianData);
            
            // Endereços
            if (!empty($data['addresses']) && is_array($data['addresses'])) {
                foreach ($data['addresses'] as $address) {
                    if (!empty(array_filter($address))) {
                        // Ensure required fields are present
                        $addressData = array_merge([
                            'is_primary' => false, // Default value
                        ], $address);
                        $guardian->addresses()->create($addressData);
                    }
                }
            }

            // Contatos
            if (!empty($data['contacts']) && is_array($data['contacts'])) {
                foreach ($data['contacts'] as $contact) {
                    if (!empty(array_filter($contact))) {
                        // Ensure required fields are present
                        $contactData = array_merge([
                            'is_primary' => false, // Default value
                            'contact_for' => 'pessoal', // Default value
                        ], $contact);
                        $guardian->contacts()->create($contactData);
                    }
                }
            }

            return $guardian;
        } catch (\Exception $e) {
            \Log::error('Erro no ServiceGuardian::create: ' . $e->getMessage());
            throw $e;
        }
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