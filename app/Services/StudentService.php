<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Guardian;
use App\Models\Classroom;
use App\Models\Enrollment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class StudentService
{
    /**
     * Listar alunos com filtros e paginação
     */
    public function getStudentsList(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Student::with(['guardians', 'activeEnrollment.classroom'])
            ->orderBy('name');

        // Aplicar filtros
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['classroom_id'])) {
            $query->byClassroom($filters['classroom_id']);
        }

        if (!empty($filters['grade'])) {
            $query->whereHas('activeEnrollment.classroom', function ($q) use ($filters) {
                $q->where('grade_level', $filters['grade']);
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Buscar aluno com relacionamentos
     */
    public function findStudentWithRelations(int $studentId): Student
    {
        return Student::with([
            'guardians' => function ($query) {
                $query->withPivot(['relationship', 'is_primary', 'is_financial', 'can_pickup']);
            },
            'enrollments.classroom.teacher',
            'activeEnrollment.classroom.teacher'
        ])->findOrFail($studentId);
    }

    /**
     * Criar novo aluno
     */
    public function createStudent(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            // Gerar número de matrícula
            $student = new Student();
            $data['registration_number'] = $student->generateRegistrationNumber();
            $data['status'] = 'active';

            // Upload da foto se fornecida
            if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
                $data['photo_path'] = $this->uploadStudentPhoto($data['photo']);
                unset($data['photo']);
            }

            // Extrair dados dos responsáveis
            $guardiansData = $data['guardians'] ?? [];
            unset($data['guardians']);

            // Extrair dados da turma
            $classroomId = $data['classroom_id'] ?? null;
            $enrollmentDate = $data['enrollment_date'] ?? now()->toDateString();
            unset($data['classroom_id']);

            // Criar aluno
            $student = Student::create($data);

            // Matricular na turma se fornecida
            if ($classroomId) {
                $this->enrollStudentInClassroom($student, $classroomId, $enrollmentDate);
            }

            // Associar responsáveis
            if (!empty($guardiansData)) {
                $this->attachGuardians($student, $guardiansData);
            }

            return $student->fresh();
        });
    }

    /**
     * Atualizar aluno
     */
    public function updateStudent(Student $student, array $data): Student
    {
        return DB::transaction(function () use ($student, $data) {
            // Upload da foto se fornecida
            if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
                // Deletar foto anterior
                if ($student->photo_path) {
                    $this->deleteStudentPhoto($student->photo_path);
                }
                $data['photo_path'] = $this->uploadStudentPhoto($data['photo']);
                unset($data['photo']);
            }

            // Extrair dados dos responsáveis
            $guardiansData = $data['guardians'] ?? [];
            unset($data['guardians']);

            // Extrair dados da turma
            $newClassroomId = $data['classroom_id'] ?? null;
            unset($data['classroom_id']);

            // Atualizar dados do aluno
            $student->update($data);

            // Transferir de turma se necessário
            if ($newClassroomId && 
                $student->activeEnrollment?->classroom_id != $newClassroomId) {
                $this->transferStudent($student, $newClassroomId);
            }

            // Atualizar responsáveis
            if (!empty($guardiansData)) {
                $student->guardians()->detach();
                $this->attachGuardians($student, $guardiansData);
            }

            return $student->fresh();
        });
    }

    /**
     * Remover aluno (soft delete)
     */
    public function deleteStudent(Student $student): bool
    {
        return DB::transaction(function () use ($student) {
            // Finalizar matrícula ativa
            if ($student->activeEnrollment) {
                $student->activeEnrollment->finish('dropped');
            }

            // Deletar foto
            if ($student->photo_path) {
                $this->deleteStudentPhoto($student->photo_path);
            }

            return $student->delete();
        });
    }

    /**
     * Transferir aluno para outra turma
     */
    public function transferStudent(Student $student, int $newClassroomId, ?string $transferDate = null): bool
    {
        return DB::transaction(function () use ($student, $newClassroomId, $transferDate) {
            $newClassroom = Classroom::findOrFail($newClassroomId);
            
            if (!$newClassroom->canEnrollStudent()) {
                throw new \Exception('A turma selecionada está lotada.');
            }

            return $student->transferToClassroom($newClassroom, $transferDate);
        });
    }

    /**
     * Alternar status do aluno
     */
    public function toggleStudentStatus(Student $student): Student
    {
        $newStatus = $student->status === 'active' ? 'inactive' : 'active';
        $student->update(['status' => $newStatus]);
        
        return $student;
    }

    /**
     * Matricular aluno em turma
     */
    public function enrollStudentInClassroom(Student $student, int $classroomId, ?string $enrollmentDate = null): Enrollment
    {
        $classroom = Classroom::findOrFail($classroomId);
        
        if (!$classroom->canEnrollStudent()) {
            throw new \Exception('A turma está lotada.');
        }

        return $classroom->enrollStudent($student, $enrollmentDate);
    }

    /**
     * Obter histórico de matrículas do aluno
     */
    public function getStudentEnrollmentHistory(Student $student): \Illuminate\Database\Eloquent\Collection
    {
        return $student->enrollments()
            ->with('classroom.teacher')
            ->orderBy('enrollment_date', 'desc')
            ->get();
    }

    /**
     * Buscar alunos disponíveis para matrícula
     */
    public function getAvailableStudentsForEnrollment(): \Illuminate\Database\Eloquent\Collection
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
     * Obter estatísticas dos alunos
     */
    public function getStudentsStats(): array
    {
        return [
            'total' => Student::count(),
            'active' => Student::active()->count(),
            'inactive' => Student::where('status', 'inactive')->count(),
            'transferred' => Student::where('status', 'transferred')->count(),
            'graduated' => Student::where('status', 'graduated')->count(),
            'new_this_month' => Student::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'enrolled_active' => Enrollment::active()->count(),
        ];
    }

    /**
     * Buscar alunos para API (autocomplete)
     */
    public function searchStudentsForApi(string $search, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Student::search($search)
            ->with('activeEnrollment.classroom')
            ->select('id', 'name', 'registration_number', 'status')
            ->limit($limit)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'registration_number' => $student->formatted_registration_number,
                    'status' => $student->status,
                    'classroom' => $student->current_classroom,
                ];
            });
    }

    /**
     * Upload da foto do aluno
     */
    private function uploadStudentPhoto(UploadedFile $photo): string
    {
        return $photo->store('students/photos', 'public');
    }

    /**
     * Deletar foto do aluno
     */
    private function deleteStudentPhoto(string $photoPath): bool
    {
        return Storage::disk('public')->delete($photoPath);
    }

    /**
     * Associar responsáveis ao aluno
     */
    private function attachGuardians(Student $student, array $guardiansData): void
    {
        // Garantir que pelo menos um seja principal
        $hasPrimary = collect($guardiansData)->contains('is_primary', true);
        if (!$hasPrimary && count($guardiansData) > 0) {
            $guardiansData[0]['is_primary'] = true;
        }

        foreach ($guardiansData as $guardianData) {
            $student->guardians()->attach($guardianData['id'], [
                'relationship' => $guardianData['relationship'],
                'is_primary' => $guardianData['is_primary'] ?? false,
                'is_financial' => $guardianData['is_financial'] ?? false,
                'can_pickup' => $guardianData['can_pickup'] ?? true,
            ]);
        }
    }

    /**
     * Validar se aluno pode ser criado/atualizado
     */
    public function validateStudentData(array $data, ?Student $student = null): array
    {
        $errors = [];

        // Verificar se CPF já existe
        if (!empty($data['cpf'])) {
            $query = Student::where('cpf', $data['cpf']);
            if ($student) {
                $query->where('id', '!=', $student->id);
            }
            if ($query->exists()) {
                $errors['cpf'] = 'Este CPF já está cadastrado.';
            }
        }

        // Verificar se email já existe
        if (!empty($data['email'])) {
            $query = Student::where('email', $data['email']);
            if ($student) {
                $query->where('id', '!=', $student->id);
            }
            if ($query->exists()) {
                $errors['email'] = 'Este email já está cadastrado.';
            }
        }

        // Verificar se turma pode receber aluno
        if (!empty($data['classroom_id'])) {
            $classroom = Classroom::find($data['classroom_id']);
            if ($classroom && !$classroom->canEnrollStudent() && 
                (!$student || $student->activeEnrollment?->classroom_id !== $classroom->id)) {
                $errors['classroom_id'] = 'A turma selecionada está lotada.';
            }
        }

        return $errors;
    }
}