<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\EnrollmentClassroomHistory;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClassroomLinkingController extends Controller
{
    public function linked(Classroom $classroom)
    {
        $enrollments = Enrollment::with(['student', 'guardian'])
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
        // Elegíveis: matrículas completas/ativas que podem ser vinculadas a esta turma
        // Inclui:
        // 1. Matrículas sem turma (classroom_id = null) - elegíveis para qualquer turma
        // 2. Matrículas com classroom_id desta turma mas sem histórico de vinculação ativo
        //    (ou seja, têm o ID da turma mas ainda não foram vinculadas oficialmente)
        
        $enrollments = Enrollment::with(['student'])
            ->where('process', 'completa')
            ->where('status', 'active')
            ->where(function($query) use ($classroom) {
                // Matrículas sem turma OU com esta turma mas sem histórico ativo
                $query->whereNull('classroom_id')
                    ->orWhere(function($q) use ($classroom) {
                        $q->where('classroom_id', $classroom->id)
                          ->whereDoesntHave('classroomHistory', function($historyQuery) use ($classroom) {
                              // Sem histórico ativo (end_date = null) para esta turma
                              $historyQuery->where('classroom_id', $classroom->id)
                                           ->whereNull('end_date');
                          });
                    });
            })
            ->orderByDesc('id')
            ->get();

        return response()->json($enrollments);
    }

    public function link(Request $request, Classroom $classroom)
    {
        $data = $request->validate([
            'enrollment_id' => ['required', 'integer', 'exists:enrollments,id'],
        ]);

        try {
            $enrollmentService = app(EnrollmentService::class);
            $enrollment = $enrollmentService->transferStudentToClassroom(
                $data['enrollment_id'],
                $classroom->id,
                false, // Vinculação direta (não precisa deixar elegível primeiro)
                'Vinculação inicial à turma'
            );

            return response()->json([
                'success' => true,
                'enrollment_id' => $enrollment->id,
                'classroom_id' => $classroom->id,
            ]);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'enrollment_id' => $e->getMessage()
            ]);
        }
    }

    public function transfer(Request $request, Classroom $classroom)
    {
        $data = $request->validate([
            'enrollment_id' => ['required', 'integer', 'exists:enrollments,id'],
            'make_eligible_first' => ['sometimes', 'boolean'], // Opção para deixar elegível primeiro
        ]);

        try {
            $enrollmentService = app(EnrollmentService::class);
            $makeEligibleFirst = $request->input('make_eligible_first', false);
            
            $enrollment = $enrollmentService->transferStudentToClassroom(
                $data['enrollment_id'],
                $classroom->id,
                $makeEligibleFirst, // Se true, desvincula primeiro (deixa elegível) antes de vincular
                'Aluno transferido para outra turma'
            );

            return response()->json([
                'success' => true,
                'enrollment_id' => $enrollment->id,
                'classroom_id' => $classroom->id,
            ]);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'enrollment_id' => $e->getMessage()
            ]);
        }
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


