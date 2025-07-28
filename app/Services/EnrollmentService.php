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
            // Verifica se já existe matrícula ativa para o aluno na turma
            $exists = Enrollment::where('student_id', $data['student_id'])
                ->where('classroom_id', $data['classroom_id'])
                ->where('status', '!=', 'cancelled')
                ->exists();
            if ($exists) {
                throw new Exception('Student is already enrolled in this classroom.');
            }

            // Verifica se há vagas na turma
            $classroom = Classroom::findOrFail($data['classroom_id']);
            $enrolledCount = Enrollment::where('classroom_id', $classroom->id)
                ->where('status', '!=', 'cancelled')
                ->count();
            if ($enrolledCount >= $classroom->vacancies) {
                throw new Exception('No vacancies available in this classroom.');
            }

            // Cria a matrícula
            return Enrollment::create([
                'student_id'      => $data['student_id'],
                'guardian_id'     => $data['guardian_id'],
                'classroom_id'    => $data['classroom_id'],
                'status'          => $data['status'] ?? 'active',
                'enrollment_date' => $data['enrollment_date'] ?? now(),
                'notes'           => $data['notes'] ?? null,
            ]);
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
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $classroom = Classroom::findOrFail($newClassroomId);
        $enrolledCount = Enrollment::where('classroom_id', $classroom->id)
            ->where('status', '!=', 'cancelled')
            ->count();
        if ($enrolledCount >= $classroom->vacancies) {
            throw new Exception('No vacancies available in the new classroom.');
        }
        $enrollment->classroom_id = $newClassroomId;
        $enrollment->save();
        return $enrollment;
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
} 