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
                        // Ensure required fields are present and handle null values
                        $addressData = array_merge([
                            'is_primary' => false, // Default value
                            'type' => 'residencial', // Default value for required field
                            'number' => '', // Default value for required field
                            'street' => '', // Default value for required field
                            'neighborhood' => '', // Default value for required field
                            'city' => '', // Default value for required field
                            'state' => '', // Default value for required field
                            'zip_code' => '', // Default value for required field
                        ], $address);
                        
                        // Convert null values to empty strings for required fields
                        $addressData['number'] = $addressData['number'] ?? '';
                        $addressData['street'] = $addressData['street'] ?? '';
                        $addressData['neighborhood'] = $addressData['neighborhood'] ?? '';
                        $addressData['city'] = $addressData['city'] ?? '';
                        $addressData['state'] = $addressData['state'] ?? '';
                        $addressData['zip_code'] = $addressData['zip_code'] ?? '';
                        $addressData['type'] = $addressData['type'] ?? 'residencial';
                        
                        // Only create address if we have at least zip_code
                        if (!empty($addressData['zip_code'])) {
                            $guardian->addresses()->create($addressData);
                        }
                    }
                }
            }

            // Contatos
            if (!empty($data['contacts']) && is_array($data['contacts'])) {
                foreach ($data['contacts'] as $contact) {
                    if (!empty(array_filter($contact))) {
                        // Ensure required fields are present and handle null values
                        $contactData = array_merge([
                            'is_primary' => false, // Default value
                            'contact_for' => 'pessoal', // Default value
                            'type' => 'phone', // Default value for required field
                            'value' => '', // Default value for required field
                        ], $contact);
                        
                        // Only create contact if we have a value
                        if (!empty($contactData['value'])) {
                            $guardian->contacts()->create($contactData);
                        }
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
     * 
     * Após vincular, detecta automaticamente irmãos se o responsável tiver múltiplos alunos
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

        // Detectar e vincular irmãos automaticamente
        $siblingService = app(\App\Services\SiblingAutoDetectionService::class);
        $siblingService->detectAndLinkSiblings($guardian, $student);
        
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