<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Classroom;
use App\Models\MonthlyFee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class EnrollmentService
{
    /**
     * Realiza uma matrícula de um aluno, vinculando um responsável.
     * IMPORTANTE: Se classroom_id for fornecido, será salvo na matrícula mas o aluno NÃO será vinculado automaticamente.
     * O aluno ficará como "elegível" e aparecerá na lista de elegíveis da turma selecionada.
     * A vinculação real deve ser feita via interface de vinculação de turmas.
     */
    public function createEnrollment(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Validar dados obrigatórios antes de qualquer operação
            $this->validateEnrollmentData($data);

            // IMPORTANTE: Se classroom_id for fornecido, salva na matrícula mas NÃO vincula o aluno
            // O aluno ficará como "elegível" e aparecerá na lista de elegíveis da turma selecionada
            // Isso permite que o usuário confirme a turma correta antes de vincular
            $classroomId = isset($data['classroom_id']) && !empty($data['classroom_id']) 
                ? $data['classroom_id'] 
                : null;
            
            // Cria a matrícula (pode ter classroom_id, mas aluno fica elegível)
            $enrollment = Enrollment::create([
                'student_id'      => $data['student_id'],
                'guardian_id'     => $data['guardian_id'],
                'classroom_id'    => $classroomId, // Pode receber o ID da turma, mas aluno fica elegível
                'academic_year'   => $data['academic_year'] ?? now()->year,
                'status'          => $data['status'] ?? 'active',
                'process'         => $data['process'] ?? 'completa',
                'enrollment_date' => $data['enrollment_date'] ?? now(),
                'notes'           => $data['notes'] ?? ($classroomId ? "Turma pretendida: {$classroomId}, aluno elegível" : null),
                ]);

            Log::info('Enrollment created as eligible', [
                    'enrollment_id' => $enrollment->id,
                    'student_id' => $data['student_id'],
                    'classroom_id' => $classroomId,
                'note' => $classroomId 
                    ? "Aluno criado com classroom_id={$classroomId} mas não vinculado. Aparecerá como elegível na turma."
                    : 'Aluno criado sem turma. Vinculação deve ser feita via /api/classrooms/{id}/link-enrollment ou interface de turmas',
                ]);

            // ✅ Mensalidades NÃO serão geradas automaticamente
            // As mensalidades devem ser geradas manualmente pelo usuário após vincular o aluno à turma
            // Isso evita gerar mensalidades incorretas caso a vinculação/transferência de turma esteja errada
            // A geração manual permite que o usuário confirme a turma correta antes de criar as mensalidades

            return $enrollment;
        });
    }
    /**
     * Cancela uma matrícula.
     */
    public function cancelEnrollment($enrollmentId, $reason = null)
    {
        return DB::transaction(function () use ($enrollmentId, $reason) {
            $enrollment = Enrollment::findOrFail($enrollmentId);
            $enrollment->status = 'cancelled';
            if ($reason) {
                $enrollment->notes = ($enrollment->notes ? $enrollment->notes . "\n" : '') . 'Cancel reason: ' . $reason;
            }
            $enrollment->save();
            
            // ✅ Cancelar mensalidades futuras
            try {
                $monthlyFeeService = app(MonthlyFeeService::class);
                $monthlyFee = MonthlyFee::where('enrollment_id', $enrollmentId)
                    ->where('status', 'active')
                    ->first();
                
                if ($monthlyFee) {
                    $monthlyFeeService->cancelMonthlyFee($monthlyFee, $reason ?? 'Matrícula cancelada');
                    
                    Log::info('Monthly fees cancelled for enrollment', [
                        'enrollment_id' => $enrollmentId,
                        'monthly_fee_id' => $monthlyFee->id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error cancelling monthly fees', [
                    'enrollment_id' => $enrollmentId,
                    'error' => $e->getMessage(),
                ]);
                // Não falha o cancelamento da matrícula se houver erro ao cancelar mensalidades
            }
            
            return $enrollment;
        });
    }
    /**
     * Método centralizado para transferir aluno de turma.
     * 
     * @param int $enrollmentId ID da matrícula
     * @param int|null $newClassroomId ID da nova turma (null para desvincular)
     * @param bool $makeEligibleFirst Se true, desvincula primeiro (deixa elegível) antes de vincular à nova turma
     * @param string|null $reason Motivo da transferência (para histórico)
     * @return Enrollment Matrícula atualizada
     */
    public function transferStudentToClassroom($enrollmentId, $newClassroomId, $makeEligibleFirst = false, $reason = null)
    {
        return DB::transaction(function () use ($enrollmentId, $newClassroomId, $makeEligibleFirst, $reason) {
            $enrollment = Enrollment::lockForUpdate()->findOrFail($enrollmentId);
            
            // Validar se matrícula pode ser vinculada/transferida
            if (!$enrollment->canBeLinkedToClassroom()) {
                throw new Exception('A matrícula não está completa/ativa para transferência.');
            }
            
            $oldClassroomId = $enrollment->classroom_id;
            
            // Se está tentando transferir para a mesma turma
            // Usar strict comparison (===) para evitar que null == 0 seja considerado igual
            if ($oldClassroomId === $newClassroomId) {
                return $enrollment; // Nada a fazer
            }
            
            // Se makeEligibleFirst = true, desvincular primeiro (deixar elegível)
            if ($makeEligibleFirst && $oldClassroomId) {
                // Finalizar histórico na turma atual
                $previousHistory = \App\Models\EnrollmentClassroomHistory::where('enrollment_id', $enrollment->id)
                    ->where('classroom_id', $oldClassroomId)
                    ->whereNull('end_date')
                    ->first();
                
                if ($previousHistory) {
                    $previousHistory->complete(now(), $reason ?? 'Aluno desvinculado para transferência');
                }
                
                // Desvincular (deixar elegível)
                $enrollment->classroom_id = null;
                $enrollment->save();
                
                // Atualizar contador da turma anterior
                Classroom::find($oldClassroomId)?->updateEnrolledCount();
                
                $oldClassroomId = null; // Agora está sem turma
            }
            
            // Se há nova turma para vincular
            if ($newClassroomId) {
                // Validar capacidade e duplicidade
                $newClassroom = Classroom::lockForUpdate()->findOrFail($newClassroomId);
                
                if (!$newClassroom->canEnrollStudent($enrollment->student_id)) {
                    throw new Exception('No vacancies available in the new classroom or student already enrolled.');
                }
                
                // Se havia turma anterior (e não foi desvinculado), finalizar histórico
                if ($oldClassroomId) {
                    $previousHistory = \App\Models\EnrollmentClassroomHistory::where('enrollment_id', $enrollment->id)
                        ->where('classroom_id', $oldClassroomId)
                        ->whereNull('end_date')
                        ->first();
                    
                    if ($previousHistory) {
                        $previousHistory->complete(now(), $reason ?? 'Aluno transferido para outra turma');
                    }
                }
                
                // Vincular à nova turma
                $enrollment->classroom_id = $newClassroomId;
                $enrollment->save();
                
                // Verificar se já existe histórico ativo (evitar duplicação)
                $existingActiveHistory = \App\Models\EnrollmentClassroomHistory::where('enrollment_id', $enrollment->id)
                    ->where('classroom_id', $newClassroomId)
                    ->whereNull('end_date')
                    ->first();
                
                // Criar novo histórico apenas se não existir
                if (!$existingActiveHistory) {
                    \App\Models\EnrollmentClassroomHistory::create([
                        'enrollment_id' => $enrollment->id,
                        'classroom_id' => $newClassroomId,
                        'start_date' => now(),
                        'end_date' => null,
                        'reason' => $oldClassroomId ? 'transfer' : 'enrollment',
                        'notes' => $reason ?? ($oldClassroomId ? 'Transferência de turma' : 'Vinculação à turma'),
                    ]);
                }
                
                // Atualizar contadores
                $newClassroom->updateEnrolledCount();
                if ($oldClassroomId) {
                    Classroom::find($oldClassroomId)?->updateEnrolledCount();
                }
            }
            
            return $enrollment;
        });
    }

    /**
     * Troca o aluno de turma (método legado - usa transferStudentToClassroom).
     * @deprecated Use transferStudentToClassroom() diretamente
     */
    public function changeClassroom($enrollmentId, $newClassroomId)
    {
        // Usar método centralizado (transferência direta, sem deixar elegível primeiro)
        return $this->transferStudentToClassroom($enrollmentId, $newClassroomId, false, 'Transferência de turma');
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
     * Se a turma for alterada, atualiza o classroom_id mas NÃO vincula automaticamente.
     * O aluno ficará como "elegível" na nova turma.
     */
    public function updateEnrollment($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $enrollment = Enrollment::findOrFail($id);
            
            // Se a turma está sendo alterada
            if (isset($data['classroom_id']) && $data['classroom_id'] != $enrollment->classroom_id) {
                $newClassroomId = $data['classroom_id'];
                $oldClassroomId = $enrollment->classroom_id;
                
                // Se havia turma anterior, desvincular primeiro
                if ($oldClassroomId) {
                    // Finalizar histórico na turma anterior (se existir)
                    $previousHistory = \App\Models\EnrollmentClassroomHistory::where('enrollment_id', $enrollment->id)
                        ->where('classroom_id', $oldClassroomId)
                        ->whereNull('end_date')
                        ->first();
                    
                    if ($previousHistory) {
                        $previousHistory->complete(now(), 'Turma alterada via edição de matrícula');
                    }
                    
                    // Atualizar contador da turma anterior
                    Classroom::find($oldClassroomId)?->updateEnrolledCount();
                }
                
                // Atualizar classroom_id na matrícula
                // IMPORTANTE: NÃO criar histórico nem atualizar contador da nova turma
                // O aluno ficará como "elegível" (tem classroom_id mas sem histórico ativo)
                $enrollment->classroom_id = $newClassroomId;
                $enrollment->save();
                
                Log::info('Classroom changed in enrollment edit', [
                    'enrollment_id' => $enrollment->id,
                    'old_classroom_id' => $oldClassroomId,
                    'new_classroom_id' => $newClassroomId,
                    'note' => 'Aluno ficou elegível na nova turma. Vinculação deve ser feita via interface de turmas.',
                ]);
                
                // Remover classroom_id do array para não atualizar novamente
                unset($data['classroom_id']);
            }
            
            // Atualizar os demais campos
            $enrollment->update($data);
            return $enrollment->fresh();
        });
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
            
            // IMPORTANTE: Na renovação, a matrícula pode receber o classroom_id
            // mas o aluno NÃO será vinculado automaticamente (não cria histórico, não atualiza contador)
            // O aluno ficará como "elegível" e aparecerá na lista de elegíveis da turma selecionada
            $classroomId = isset($data['classroom_id']) && !empty($data['classroom_id']) 
                ? $data['classroom_id'] 
                : null;
            
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
            // Nota: Cada ano letivo tem sua própria matrícula, não preservamos histórico entre anos
            // Se classroom_id foi fornecido, salva na matrícula mas NÃO vincula o aluno
            $newEnrollment = Enrollment::create([
                'student_id'      => $previousEnrollment->student_id,
                'guardian_id'     => $data['guardian_id'] ?? $previousEnrollment->guardian_id,
                'classroom_id'    => $classroomId, // Pode receber o ID da turma, mas aluno fica elegível
                'academic_year'   => $newYear,
                'status'          => 'active',
                'process'         => 'renovacao',
                'enrollment_date' => now(),
                'notes'           => "Renovação da matrícula #{$previousEnrollmentId} do ano {$previousEnrollment->academic_year}" .
                                   ($classroomId ? " (Turma pretendida: {$classroomId}, aluno elegível)" : ''),
            ]);
            
            // 3. IMPORTANTE: NÃO criar histórico de vinculação nem atualizar contador da turma
            // O aluno terá o classroom_id na matrícula, mas não estará vinculado
            // Ele aparecerá como elegível na lista de elegíveis da turma selecionada
            // A vinculação real deve ser feita via interface de vinculação de turmas
            
            // Log da renovação
            Log::info('Enrollment renewed', [
                'old_enrollment_id' => $previousEnrollmentId,
                'new_enrollment_id' => $newEnrollment->id,
                'student_id' => $newEnrollment->student_id,
                'old_year' => $previousEnrollment->academic_year,
                'new_year' => $newYear,
                'classroom_id' => $classroomId,
                'note' => $classroomId 
                    ? "Matrícula criada com classroom_id={$classroomId}, mas aluno não vinculado (elegível)" 
                    : 'Aluno criado como elegível sem turma definida',
            ]);
            
            // ✅ Mensalidades NÃO serão geradas automaticamente na renovação
            // As mensalidades devem ser geradas manualmente pelo usuário após vincular o aluno à turma
            // Isso evita gerar mensalidades incorretas caso a vinculação/transferência de turma esteja errada
            // As mensalidades serão geradas apenas quando o aluno for vinculado a uma turma
            // Isso acontece automaticamente quando o aluno é vinculado via /api/classrooms/{id}/link-enrollment
            
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