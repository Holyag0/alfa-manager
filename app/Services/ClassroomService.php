<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ClassroomService
{
    /**
     * Listar turmas com filtros e paginação
     */
    public function getClassroomsList(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Classroom::with(['teacher', 'activeEnrollments'])
            ->withCount(['activeEnrollments as students_count'])
            ->orderBy('grade_level')
            ->orderBy('section');

        // Aplicar filtros
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['grade'])) {
            $query->byGrade($filters['grade']);
        }

        if (!empty($filters['year'])) {
            $query->where('school_year', $filters['year']);
        } else {
            $query->currentYear();
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['availability'])) {
            if ($filters['availability'] === 'available') {
                $query->withAvailableSpots();
            } elseif ($filters['availability'] === 'full') {
                $query->whereRaw('current_students >= max_students');
            }
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Buscar turma com relacionamentos
     */
    public function findClassroomWithRelations(int $classroomId): Classroom
    {
        return Classroom::with([
            'teacher',
            'students' => function ($query) {
                $query->with(['guardians' => function ($q) {
                    $q->wherePivot('is_primary', true);
                }])->orderBy('name');
            }
        ])->findOrFail($classroomId);
    }

    /**
     * Criar nova turma
     */
    public function createClassroom(array $data): Classroom
    {
        return DB::transaction(function () use ($data) {
            // Gerar nome da turma
            $data['name'] = Classroom::generateName($data['grade_level'], $data['section']);
            $data['status'] = 'active';
            $data['current_students'] = 0;

            // Verificar se já existe turma com mesmo nome no ano
            $this->validateClassroomUniqueness($data['name'], $data['school_year']);

            // Verificar conflito de horário do professor
            if (!empty($data['teacher_id']) && !empty($data['schedule'])) {
                $this->validateTeacherSchedule($data['teacher_id'], $data['schedule'], $data['school_year']);
            }

            return Classroom::create($data);
        });
    }

    /**
     * Atualizar turma
     */
    public function updateClassroom(Classroom $classroom, array $data): Classroom
    {
        return DB::transaction(function () use ($classroom, $data) {
            // Gerar novo nome se mudou série ou seção
            $newName = Classroom::generateName($data['grade_level'], $data['section']);
            
            if ($newName !== $classroom->name) {
                $data['name'] = $newName;
                $this->validateClassroomUniqueness($newName, $data['school_year'], $classroom->id);
            }

            // Verificar se a capacidade não é menor que o número atual de alunos
            if ($data['max_students'] < $classroom->current_students) {
                throw new \Exception(
                    'A capacidade não pode ser menor que o número atual de alunos (' . 
                    $classroom->current_students . ').'
                );
            }

            // Verificar conflito de horário do professor
            if (!empty($data['teacher_id']) && !empty($data['schedule'])) {
                $this->validateTeacherSchedule(
                    $data['teacher_id'], 
                    $data['schedule'], 
                    $data['school_year'], 
                    $classroom->id
                );
            }

            $classroom->update($data);
            return $classroom->fresh();
        });
    }

    /**
     * Remover turma
     */
    public function deleteClassroom(Classroom $classroom): bool
    {
        // Verificar se há alunos matriculados
        if ($classroom->current_students > 0) {
            throw new \Exception('Não é possível excluir uma turma com alunos matriculados.');
        }

        return $classroom->delete();
    }

    /**
     * Matricular aluno na turma
     */
    public function enrollStudent(Classroom $classroom, int $studentId, ?string $enrollmentDate = null): Enrollment
    {
        return DB::transaction(function () use ($classroom, $studentId, $enrollmentDate) {
            $student = Student::findOrFail($studentId);

            if (!$classroom->canEnrollStudent()) {
                throw new \Exception('A turma está lotada.');
            }

            // Verificar se o aluno já está matriculado em outra turma
            if ($student->activeEnrollment) {
                throw new \Exception('O aluno já está matriculado em outra turma.');
            }

            return $classroom->enrollStudent($student, $enrollmentDate);
        });
    }

    /**
     * Remover aluno da turma
     */
    public function removeStudent(Classroom $classroom, Student $student, string $reason = 'transferred', ?string $endDate = null): bool
    {
        return DB::transaction(function () use ($classroom, $student, $reason, $endDate) {
            return $classroom->removeStudent($student, $reason);
        });
    }

    /**
     * Duplicar turma para próximo ano
     */
    public function duplicateClassroom(Classroom $classroom, ?int $newYear = null): Classroom
    {
        $newYear = $newYear ?? (now()->year + 1);

        // Verificar se já existe uma turma igual no novo ano
        $existingClassroom = Classroom::where('name', $classroom->name)
            ->where('school_year', $newYear)
            ->first();

        if ($existingClassroom) {
            throw new \Exception('Já existe uma turma com este nome no ano ' . $newYear . '.');
        }

        $newClassroom = $classroom->replicate();
        $newClassroom->school_year = $newYear;
        $newClassroom->current_students = 0;
        $newClassroom->save();

        return $newClassroom;
    }

    /**
     * Atualizar contador de alunos da turma
     */
    public function updateStudentCount(Classroom $classroom): Classroom
    {
        $classroom->updateStudentCount();
        return $classroom->fresh();
    }

    /**
     * Obter alunos disponíveis para matrícula
     */
    public function getAvailableStudentsForEnrollment()
    {
        return Student::active()
            ->whereDoesntHave('enrollments', function ($query) {
                $query->where('status', 'active');
            })
            ->select('id', 'name', 'registration_number')
            ->orderBy('name')
            ->get();
    }

    /**
     * Obter turmas com vagas disponíveis
     */
    public function getAvailableClassrooms(?int $year = null)
    {
        $query = Classroom::active()->withAvailableSpots()->with('teacher');
        
        if ($year) {
            $query->where('school_year', $year);
        } else {
            $query->currentYear();
        }

        return $query->get()->map(function ($classroom) {
            return [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'teacher' => $classroom->teacher?->name,
                'available_spots' => $classroom->available_spots,
                'grade_level' => $classroom->grade_level,
                'occupancy' => "{$classroom->current_students}/{$classroom->max_students}",
            ];
        });
    }

    /**
     * Obter estatísticas das turmas
     */
    public function getClassroomsStats(?int $year = null): array
    {
        $year = $year ?? now()->year;

        $totalStudents = Classroom::where('school_year', $year)->sum('current_students');
        $totalCapacity = Classroom::where('school_year', $year)->sum('max_students');
        $occupancyRate = $totalCapacity > 0 ? round(($totalStudents / $totalCapacity) * 100, 1) : 0;

        return [
            'total' => Classroom::where('school_year', $year)->count(),
            'active' => Classroom::where('school_year', $year)->active()->count(),
            'with_spots' => Classroom::where('school_year', $year)->withAvailableSpots()->count(),
            'full' => Classroom::where('school_year', $year)->whereRaw('current_students >= max_students')->count(),
            'total_students' => $totalStudents,
            'total_capacity' => $totalCapacity,
            'occupancy_rate' => $occupancyRate,
            'average_class_size' => $totalStudents > 0 ? round($totalStudents / Classroom::where('school_year', $year)->active()->count(), 1) : 0,
        ];
    }

    /**
     * Buscar turmas para API (autocomplete)
     */
    public function searchClassroomsForApi(string $search, int $limit = 10)
    {
        return Classroom::search($search)
            ->active()
            ->currentYear()
            ->with('teacher')
            ->select('id', 'name', 'grade_level', 'section', 'current_students', 'max_students', 'teacher_id')
            ->limit($limit)
            ->get()
            ->map(function ($classroom) {
                return [
                    'id' => $classroom->id,
                    'name' => $classroom->name,
                    'grade_level' => $classroom->grade_level,
                    'teacher' => $classroom->teacher?->name,
                    'occupancy' => "{$classroom->current_students}/{$classroom->max_students}",
                    'available_spots' => $classroom->available_spots,
                    'can_enroll' => $classroom->canEnrollStudent(),
                ];
            });
    }

    /**
     * Obter séries/anos disponíveis
     */
    public function getAvailableGrades(?int $year = null)
    {
        $query = Classroom::distinct();
        
        if ($year) {
            $query->where('school_year', $year);
        } else {
            $query->currentYear();
        }

        return $query->pluck('grade_level')->sort()->values();
    }

    /**
     * Obter anos letivos disponíveis
     */
    public function getAvailableYears()
    {
        return Classroom::distinct()
            ->pluck('school_year')
            ->sort()
            ->values();
    }

    /**
     * Obter professores disponíveis
     */
    public function getAvailableTeachers()
    {
        return User::where('is_auth', 1)
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();
    }

    /**
     * Validar se nome da turma é único no ano
     */
    private function validateClassroomUniqueness(string $name, int $year, ?int $excludeId = null): void
    {
        $query = Classroom::where('name', $name)->where('school_year', $year);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new \Exception('Já existe uma turma ' . $name . ' no ano ' . $year . '.');
        }
    }

    /**
     * Validar conflito de horário do professor
     */
    private function validateTeacherSchedule(int $teacherId, array $schedule, int $year, ?int $excludeClassroomId = null): void
    {
        $conflictingClassrooms = Classroom::where('teacher_id', $teacherId)
            ->where('school_year', $year)
            ->where('status', 'active')
            ->when($excludeClassroomId, function ($query) use ($excludeClassroomId) {
                $query->where('id', '!=', $excludeClassroomId);
            })
            ->get();

        foreach ($conflictingClassrooms as $classroom) {
            if ($this->hasScheduleConflict($classroom->schedule, $schedule)) {
                throw new \Exception(
                    'Este professor já tem conflito de horário com a turma ' . $classroom->name . '.'
                );
            }
        }
    }

    /**
     * Verificar conflito entre horários
     */
    private function hasScheduleConflict(?array $schedule1, array $schedule2): bool
    {
        if (!$schedule1 || !$schedule2) {
            return false;
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        foreach ($days as $day) {
            if (isset($schedule1[$day]) && isset($schedule2[$day])) {
                foreach ($schedule1[$day] as $time1) {
                    foreach ($schedule2[$day] as $time2) {
                        if ($this->timeRangesOverlap($time1, $time2)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Verificar sobreposição de horários
     */
    private function timeRangesOverlap(string $range1, string $range2): bool
    {
        if (!$range1 || !$range2) {
            return false;
        }

        [$start1, $end1] = explode('-', $range1);
        [$start2, $end2] = explode('-', $range2);

        return $start1 < $end2 && $start2 < $end1;
    }

    /**
     * Gerar relatório da turma
     */
    public function generateClassroomReport(Classroom $classroom): array
    {
        $classroom->load([
            'teacher',
            'students' => function ($query) {
                $query->with(['guardians' => function ($q) {
                    $q->wherePivot('is_primary', true);
                }])->orderBy('name');
            }
        ]);

        return [
            'classroom' => $classroom,
            'stats' => [
                'total_students' => $classroom->current_students,
                'capacity' => $classroom->max_students,
                'occupancy_rate' => $classroom->occupancy_percentage,
                'available_spots' => $classroom->available_spots,
                'boys_count' => $classroom->students->where('gender', 'M')->count(),
                'girls_count' => $classroom->students->where('gender', 'F')->count(),
                'average_age' => $classroom->students->avg('age'),
            ],
            'students_by_guardian' => $classroom->students->groupBy(function ($student) {
                return $student->getPrimaryGuardian()?->name ?? 'Sem responsável';
            }),
        ];
    }
}