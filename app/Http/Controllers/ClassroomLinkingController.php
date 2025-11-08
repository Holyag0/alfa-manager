<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\EnrollmentClassroomHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClassroomLinkingController extends Controller
{
    public function linked(Classroom $classroom)
    {
        $enrollments = Enrollment::with(['student'])
            ->where('classroom_id', $classroom->id)
            ->where('process', 'completa')
            ->where('status', 'active')
            ->orderByDesc('id')
            ->get();

        return response()->json($enrollments);
    }

    /**
     * Listar TODOS os alunos que passaram pela turma (incluindo transferidos)
     */
    public function history(Classroom $classroom)
    {
        $history = EnrollmentClassroomHistory::with(['enrollment.student', 'enrollment.guardian'])
            ->where('classroom_id', $classroom->id)
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function($record) {
                return [
                    'id' => $record->id,
                    'enrollment_id' => $record->enrollment_id,
                    'student' => $record->enrollment->student,
                    'guardian' => $record->enrollment->guardian,
                    'start_date' => $record->start_date->format('d/m/Y'),
                    'end_date' => $record->end_date ? $record->end_date->format('d/m/Y') : null,
                    'reason' => $record->reason,
                    'status' => $record->isActive() ? 'Ativo' : 'Transferido/Saiu',
                    'notes' => $record->notes,
                ];
            });

        return response()->json($history);
    }

    public function eligible(Classroom $classroom)
    {
        // Elegíveis: matrículas completas/ativas sem turma vinculada
        $enrollments = Enrollment::with(['student'])
            ->whereNull('classroom_id')
            ->where('process', 'completa')
            ->where('status', 'active')
            ->orderByDesc('id')
            ->get();

        return response()->json($enrollments);
    }

    public function link(Request $request, Classroom $classroom)
    {
        $data = $request->validate([
            'enrollment_id' => ['required', 'integer', 'exists:enrollments,id'],
        ]);

        return DB::transaction(function () use ($data, $classroom) {
            /** @var Enrollment $enrollment */
            $enrollment = Enrollment::lockForUpdate()->findOrFail($data['enrollment_id']);

            if (!$enrollment->canBeLinkedToClassroom()) {
                throw ValidationException::withMessages([
                    'enrollment_id' => 'A matrícula não está completa/ativa para vinculação.'
                ]);
            }

            if (!$classroom->canEnrollStudent($enrollment->student_id)) {
                throw ValidationException::withMessages([
                    'classroom_id' => 'Turma sem vagas ou aluno já vinculado.'
                ]);
            }

            $enrollment->classroom_id = $classroom->id;
            $enrollment->save();

            // Registrar no histórico de turmas
            EnrollmentClassroomHistory::create([
                'enrollment_id' => $enrollment->id,
                'classroom_id' => $classroom->id,
                'start_date' => now(),
                'end_date' => null,
                'reason' => 'enrollment',
                'notes' => 'Vinculação inicial à turma',
            ]);

            // Atualiza contadores
            $classroom->updateEnrolledCount();

            return response()->json([
                'success' => true,
                'enrollment_id' => $enrollment->id,
                'classroom_id' => $classroom->id,
            ]);
        });
    }

    public function transfer(Request $request, Classroom $classroom)
    {
        $data = $request->validate([
            'enrollment_id' => ['required', 'integer', 'exists:enrollments,id'],
        ]);

        return DB::transaction(function () use ($data, $classroom) {
            /** @var Enrollment $enrollment */
            $enrollment = Enrollment::lockForUpdate()->findOrFail($data['enrollment_id']);

            if (!$enrollment->canBeLinkedToClassroom()) {
                throw ValidationException::withMessages([
                    'enrollment_id' => 'A matrícula não está completa/ativa para transferência.'
                ]);
            }

            $fromClassroomId = $enrollment->classroom_id;
            if ($fromClassroomId === $classroom->id) {
                return response()->json(['success' => true]);
            }

            if (!$classroom->canEnrollStudent($enrollment->student_id)) {
                throw ValidationException::withMessages([
                    'classroom_id' => 'Turma de destino sem vagas ou aluno já vinculado.'
                ]);
            }

            // Finalizar histórico na turma anterior
            if ($fromClassroomId) {
                $previousHistory = EnrollmentClassroomHistory::where('enrollment_id', $enrollment->id)
                    ->where('classroom_id', $fromClassroomId)
                    ->whereNull('end_date')
                    ->first();
                
                if ($previousHistory) {
                    $previousHistory->complete(now(), 'Aluno transferido para outra turma');
                }
            }

            $enrollment->classroom_id = $classroom->id;
            $enrollment->save();

            // Criar novo histórico na turma nova
            EnrollmentClassroomHistory::create([
                'enrollment_id' => $enrollment->id,
                'classroom_id' => $classroom->id,
                'start_date' => now(),
                'end_date' => null,
                'reason' => 'transfer',
                'notes' => $fromClassroomId ? "Transferido da turma ID {$fromClassroomId}" : 'Transferido de outra turma',
            ]);

            // Atualiza contadores em ambas as turmas
            $classroom->updateEnrolledCount();
            if ($fromClassroomId) {
                Classroom::find($fromClassroomId)?->updateEnrolledCount();
            }

            return response()->json([
                'success' => true,
                'enrollment_id' => $enrollment->id,
                'classroom_id' => $classroom->id,
            ]);
        });
    }

    public function unlink(Request $request, Classroom $classroom)
    {
        $data = $request->validate([
            'enrollment_id' => ['required', 'integer', 'exists:enrollments,id'],
        ]);

        return DB::transaction(function () use ($data, $classroom) {
            /** @var Enrollment $enrollment */
            $enrollment = Enrollment::lockForUpdate()->findOrFail($data['enrollment_id']);

            // Verificar se a matrícula pertence à turma
            if ($enrollment->classroom_id !== $classroom->id) {
                throw ValidationException::withMessages([
                    'enrollment_id' => 'Esta matrícula não está vinculada a esta turma.'
                ]);
            }

            // Finalizar histórico na turma antes de desvincular
            $previousHistory = EnrollmentClassroomHistory::where('enrollment_id', $enrollment->id)
                ->where('classroom_id', $classroom->id)
                ->whereNull('end_date')
                ->first();
            
            if ($previousHistory) {
                $previousHistory->complete(now(), 'Aluno desvinculado da turma');
            }

            // Desvincular (remover turma)
            $enrollment->classroom_id = null;
            $enrollment->save();

            // Atualizar contador da turma
            $classroom->updateEnrolledCount();

            return response()->json([
                'success' => true,
                'message' => 'Matrícula desvinculada com sucesso!',
                'enrollment_id' => $enrollment->id,
            ]);
        });
    }
}


