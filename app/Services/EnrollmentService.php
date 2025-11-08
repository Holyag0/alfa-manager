<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Classroom;
use Illuminate\Support\Facades\DB;
use Exception;

class EnrollmentService
{
    /**
     * Realiza uma matrícula de um aluno em uma turma, vinculando um responsável.
     */
    public function createEnrollment(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Validar dados obrigatórios antes de qualquer operação
            $this->validateEnrollmentData($data);

            // Determinar classroom_id: aceitar se fornecido, senão null
            $classroomId = $data['classroom_id'] ?? null;
            
            // Cria a matrícula
            $enrollment = Enrollment::create([
                'student_id'      => $data['student_id'],
                'guardian_id'     => $data['guardian_id'],
                'classroom_id'    => $classroomId,
                'academic_year'   => $data['academic_year'] ?? now()->year,
                'status'          => $data['status'] ?? 'active',
                'process'         => $data['process'] ?? 'completa',
                'enrollment_date' => $data['enrollment_date'] ?? now(),
                'notes'           => $data['notes'] ?? null,
            ]);

            // Se turma foi fornecida, criar histórico e atualizar contador
            if ($classroomId) {
                // Criar registro de histórico
                \App\Models\EnrollmentClassroomHistory::create([
                    'enrollment_id' => $enrollment->id,
                    'classroom_id' => $classroomId,
                    'start_date' => $enrollment->enrollment_date ?? now(),
                    'end_date' => null,
                    'reason' => 'enrollment',
                    'notes' => 'Vinculação inicial à turma durante matrícula',
                ]);

                // Atualizar contador da turma
                $classroom = Classroom::find($classroomId);
                if ($classroom) {
                    $classroom->updateEnrolledCount();
                }

                \Log::info('Enrollment created WITH classroom', [
                    'enrollment_id' => $enrollment->id,
                    'student_id' => $data['student_id'],
                    'classroom_id' => $classroomId,
                    'note' => 'Turma vinculada automaticamente durante criação',
                ]);
            } else {
                \Log::info('Enrollment created WITHOUT classroom', [
                    'enrollment_id' => $enrollment->id,
                    'student_id' => $data['student_id'],
                    'note' => 'Turma pode ser vinculada posteriormente via /api/classrooms/{id}/link-enrollment',
                ]);
            }

            return $enrollment;
        });
    }
    /**
     * Cancela uma matrícula.
     */
    public function cancelEnrollment($enrollmentId, $reason = null)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $enrollment->status = 'cancelled';
        if ($reason) {
            $enrollment->notes = ($enrollment->notes ? $enrollment->notes . "\n" : '') . 'Cancel reason: ' . $reason;
        }
        $enrollment->save();
        return $enrollment;
    }
    /**
     * Troca o aluno de turma.
     */
    public function changeClassroom($enrollmentId, $newClassroomId)
    {
        return DB::transaction(function () use ($enrollmentId, $newClassroomId) {
            $enrollment = Enrollment::findOrFail($enrollmentId);
            
            // Verificar se a nova turma é diferente da atual
            if ($enrollment->classroom_id == $newClassroomId) {
                throw new Exception('Student is already enrolled in this classroom.');
            }
            
            // Lock pessimista na nova turma para evitar race condition
            $newClassroom = Classroom::lockForUpdate()->findOrFail($newClassroomId);

            // Validar capacidade e duplicidade usando a regra centralizada
            if (!$newClassroom->canEnrollStudent($enrollment->student_id)) {
                throw new Exception('No vacancies available in the new classroom or student already enrolled.');
            }

            $oldClassroomId = $enrollment->classroom_id;

            // Contagens antes
            $beforeNewCount = $newClassroom->getEnrolledStudentsCount();
            $beforeOldCount = $oldClassroomId ? Classroom::find($oldClassroomId)?->getEnrolledStudentsCount() : null;

            // Efetivar transferência
            $enrollment->classroom_id = $newClassroomId;
            $enrollment->save();

            // Atualizar contadores das turmas
            $newClassroom->updateEnrolledCount();
            if ($oldClassroomId) {
                Classroom::find($oldClassroomId)?->updateEnrolledCount();
            }

            // Log da mudança para auditoria
            \Log::info('Enrollment classroom changed', [
                'enrollment_id' => $enrollment->id,
                'student_id' => $enrollment->student_id,
                'old_classroom_id' => $oldClassroomId,
                'new_classroom_id' => $newClassroomId,
                'before_new_count' => $beforeNewCount,
                'after_new_count' => $newClassroom->current_students,
                'before_old_count' => $beforeOldCount,
            ]);
            
            return $enrollment;
        });
    }
    /**
     * Consulta matrículas por filtros, com paginação.
     */
    public function searchEnrollments(array $filters = [], $perPage = 10)
    {
        $query = Enrollment::with(['student', 'guardian', 'classroom']);
        if (!empty($filters['student'])) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['student'] . '%');
            });
        }
        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }
        if (!empty($filters['guardian_id'])) {
            $query->where('guardian_id', $filters['guardian_id']);
        }
        if (!empty($filters['classroom_id'])) {
            $query->where('classroom_id', $filters['classroom_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['process'])) {
            $query->where('process', $filters['process']);
        }
        
        // Ordenar por ID desc (mais recentes primeiro)
        $query->orderBy('id', 'desc');
        
        return $query->paginate($perPage);
    }

    /**
     * Atualiza uma matrícula existente.
     */
    public function updateEnrollment($id, array $data)
    {
        $enrollment = Enrollment::findOrFail($id);
        $enrollment->update($data);
        return $enrollment;
    }

    /**
     * Renovar matrícula para novo ano letivo
     */
    public function renewEnrollment($previousEnrollmentId, array $data)
    {
        return DB::transaction(function () use ($previousEnrollmentId, $data) {
            $previousEnrollment = Enrollment::findOrFail($previousEnrollmentId);
            
            // Verificar se a matrícula pode ser renovada
            if (!$previousEnrollment->canBeRenewed()) {
                throw new Exception('Esta matrícula não pode ser renovada. Status: ' . $previousEnrollment->status);
            }
            
            // Validar ano letivo
            $newYear = $data['academic_year'] ?? (now()->year + 1);
            
            // Verificar se já existe matrícula ativa para este aluno neste ano
            if (Enrollment::hasActiveEnrollmentInYear($previousEnrollment->student_id, $newYear)) {
                throw new Exception("Aluno já possui matrícula ativa para o ano {$newYear}.");
            }
            
            // 1. Finalizar matrícula anterior (se ainda ativa)
            if ($previousEnrollment->status === 'active') {
                $previousEnrollment->status = 'completed';
                $previousEnrollment->notes = ($previousEnrollment->notes ? $previousEnrollment->notes . "\n" : '') 
                    . "Matrícula finalizada em " . now()->format('d/m/Y') . " - Renovada para {$newYear}";
                $previousEnrollment->save();
                
                // Atualizar contador da turma anterior
                if ($previousEnrollment->classroom_id) {
                    Classroom::find($previousEnrollment->classroom_id)?->updateEnrolledCount();
                }
            }
            
            // 2. Criar nova matrícula para o novo ano
            $newEnrollment = Enrollment::create([
                'student_id'      => $previousEnrollment->student_id,
                'guardian_id'     => $data['guardian_id'] ?? $previousEnrollment->guardian_id,
                'classroom_id'    => $data['classroom_id'] ?? null,
                'academic_year'   => $newYear,
                'status'          => 'active',
                'process'         => 'renovacao',
                'enrollment_date' => now(),
                'notes'           => "Renovação da matrícula #{$previousEnrollmentId} do ano {$previousEnrollment->academic_year}",
            ]);
            
            // 3. Atualizar contador da nova turma (se fornecida)
            if ($newEnrollment->classroom_id) {
                Classroom::find($newEnrollment->classroom_id)?->updateEnrolledCount();
            }
            
            // Log da renovação
            \Log::info('Enrollment renewed', [
                'old_enrollment_id' => $previousEnrollmentId,
                'new_enrollment_id' => $newEnrollment->id,
                'student_id' => $newEnrollment->student_id,
                'old_year' => $previousEnrollment->academic_year,
                'new_year' => $newYear,
            ]);
            
            return $newEnrollment;
        });
    }

    /**
     * Valida os dados obrigatórios para criação de matrícula.
     */
    private function validateEnrollmentData(array $data)
    {
        // Validar campos obrigatórios (classroom_id não é mais obrigatório - vinculação via sistema dedicado)
        $requiredFields = ['student_id', 'guardian_id'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '{$field}' is required.");
            }
        }

        // Validar se o aluno existe
        if (!Student::find($data['student_id'])) {
            throw new Exception('Student not found.');
        }

        // Validar se o responsável existe
        if (!Guardian::find($data['guardian_id'])) {
            throw new Exception('Guardian not found.');
        }

        // Validar se a turma existe (se fornecida - não é mais obrigatória)
        if (isset($data['classroom_id']) && !empty($data['classroom_id'])) {
            if (!Classroom::find($data['classroom_id'])) {
                throw new Exception('Classroom not found.');
            }
        }

        // Validar formato da data se fornecida
        if (isset($data['enrollment_date']) && !strtotime($data['enrollment_date'])) {
            throw new Exception('Invalid enrollment date format.');
        }

        // Validar status se fornecido
        if (isset($data['status']) && !in_array($data['status'], ['active', 'pending', 'cancelled', 'inactive', 'completed'])) {
            throw new Exception('Invalid status value.');
        }

        // Validar processo se fornecido
        $validProcesses = ['reserva', 'aguardando_pagamento', 'aguardando_documentos', 'desistencia', 'transferencia', 'renovacao', 'completa'];
        if (isset($data['process']) && !in_array($data['process'], $validProcesses)) {
            throw new Exception('Invalid process value.');
        }
        
        // Validar ano letivo se fornecido
        if (isset($data['academic_year'])) {
            $year = (int) $data['academic_year'];
            $currentYear = (int) now()->year;
            if ($year < 2000 || $year > ($currentYear + 5)) {
                throw new Exception('Invalid academic year. Must be between 2000 and ' . ($currentYear + 5));
            }
        }
        
        // Verificar se já existe matrícula ativa para este aluno neste ano
        $academicYear = $data['academic_year'] ?? now()->year;
        if (Enrollment::hasActiveEnrollmentInYear($data['student_id'], $academicYear)) {
            throw new Exception("Aluno já possui matrícula ativa para o ano {$academicYear}.");
        }
    }
} 