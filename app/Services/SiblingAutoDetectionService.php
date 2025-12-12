<?php

namespace App\Services;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\SiblingGroup;
use Illuminate\Support\Facades\Log;

class SiblingAutoDetectionService
{
    /**
     * Detectar e vincular automaticamente irmãos quando um responsável tem múltiplos alunos
     * 
     * Esta função é chamada quando:
     * 1. Um responsável é vinculado a um aluno
     * 2. Uma matrícula é criada
     * 
     * @param Guardian $guardian O responsável que foi vinculado
     * @param Student|null $newStudent O aluno recém-vinculado (opcional, para evitar processamento desnecessário)
     */
    public function detectAndLinkSiblings(Guardian $guardian, ?Student $newStudent = null)
    {
        try {
            // Buscar todos os alunos vinculados a este responsável
            $students = $guardian->students()->get();

            // Se o responsável tem menos de 2 alunos, não há irmãos para detectar
            if ($students->count() < 2) {
                return;
            }

            // Buscar ou criar grupo de irmãos para este responsável
            $siblingGroup = $this->getOrCreateSiblingGroup($guardian, $students);

            // Buscar todos os responsáveis que têm os mesmos alunos
            $relatedGuardians = $this->getRelatedGuardians($students);

            // Adicionar todos os responsáveis relacionados ao mesmo grupo
            foreach ($relatedGuardians as $relatedGuardian) {
                if (!$relatedGuardian->sibling_group_id) {
                    $relatedGuardian->update(['sibling_group_id' => $siblingGroup->id]);
                }
            }

            Log::info('Irmãos detectados automaticamente', [
                'guardian_id' => $guardian->id,
                'guardian_name' => $guardian->name,
                'students_count' => $students->count(),
                'sibling_group_id' => $siblingGroup->id,
                'new_student_id' => $newStudent?->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao detectar irmãos automaticamente', [
                'guardian_id' => $guardian->id,
                'error' => $e->getMessage(),
            ]);
            // Não lança exceção para não quebrar o fluxo principal
        }
    }

    /**
     * Obter ou criar grupo de irmãos
     */
    protected function getOrCreateSiblingGroup(Guardian $guardian, $students)
    {
        // Se o responsável já tem um grupo, usar esse grupo
        if ($guardian->sibling_group_id) {
            return $guardian->siblingGroup;
        }

        // Verificar se algum dos outros responsáveis dos alunos já tem um grupo
        foreach ($students as $student) {
            foreach ($student->guardians as $studentGuardian) {
                if ($studentGuardian->sibling_group_id && $studentGuardian->id !== $guardian->id) {
                    // Usar o grupo existente
                    $guardian->update(['sibling_group_id' => $studentGuardian->sibling_group_id]);
                    return $studentGuardian->siblingGroup;
                }
            }
        }

        // Criar novo grupo de irmãos
        $studentNames = $students->pluck('name')->take(3)->implode(', ');
        $groupName = $students->count() > 3 
            ? "Grupo de {$studentNames} e mais " . ($students->count() - 3) . " aluno(s)"
            : "Grupo de {$studentNames}";

        $group = SiblingGroup::create([
            'name' => $groupName,
            'description' => 'Grupo de irmãos criado automaticamente pela detecção de responsável comum',
            'is_active' => true,
        ]);

        // Adicionar o responsável ao grupo
        $guardian->update(['sibling_group_id' => $group->id]);

        return $group;
    }

    /**
     * Obter todos os responsáveis que têm os mesmos alunos
     */
    protected function getRelatedGuardians($students)
    {
        $guardianIds = collect();

        foreach ($students as $student) {
            $studentGuardianIds = $student->guardians()->pluck('guardians.id');
            if ($guardianIds->isEmpty()) {
                $guardianIds = $studentGuardianIds;
            } else {
                // Intersecção: apenas responsáveis que estão em TODOS os alunos
                $guardianIds = $guardianIds->intersect($studentGuardianIds);
            }
        }

        // Se não há responsáveis comuns a todos, buscar responsáveis que estão em pelo menos 2 alunos
        if ($guardianIds->isEmpty()) {
            $guardianCounts = [];
            foreach ($students as $student) {
                foreach ($student->guardians as $guardian) {
                    $guardianCounts[$guardian->id] = ($guardianCounts[$guardian->id] ?? 0) + 1;
                }
            }
            
            // Filtrar responsáveis que aparecem em pelo menos 2 alunos
            $guardianIds = collect($guardianCounts)
                ->filter(fn($count) => $count >= 2)
                ->keys();
        }

        return Guardian::whereIn('id', $guardianIds)->get();
    }

    /**
     * Detectar irmãos para um aluno específico
     * Útil quando um aluno é adicionado e queremos verificar seus irmãos
     */
    public function detectSiblingsForStudent(Student $student)
    {
        // Buscar todos os responsáveis do aluno
        $guardians = $student->guardians;

        foreach ($guardians as $guardian) {
            $this->detectAndLinkSiblings($guardian, $student);
        }
    }
}

