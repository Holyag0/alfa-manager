<?php

namespace App\Services;

use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class GuardianService extends BaseService
{
    protected string $cachePrefix = 'guardian_service';
    /**
     * Listar responsáveis com filtros
     */
    public function getGuardiansList(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->executeWithLog('list_guardians', function () use ($filters, $perPage) {
            $query = Guardian::with(['students' => function ($q) {
                $q->select('id', 'name', 'registration_number', 'status');
            }])->orderBy('name');

            if (!empty($filters['search'])) {
                $query->search($filters['search']);
            }

            return $query->paginate($perPage)->withQueryString();
        });
    }
    /**
     * Buscar responsável com relacionamentos
     */
    public function findGuardianWithRelations(int $guardianId): Guardian
    {
        return $this->executeWithLog('find_guardian', function () use ($guardianId) {
            return Guardian::with([
                'students' => function ($query) {
                    $query->withPivot(['relationship', 'is_primary', 'is_financial', 'can_pickup'])
                          ->with('activeEnrollment.classroom');
                }
            ])->findOrFail($guardianId);
        }, ['guardian_id' => $guardianId]);
    }
    /**
     * Criar responsável
     */
    public function createGuardian(array $data): Guardian
    {
        return $this->executeWithLog('create_guardian', function () use ($data) {
            return DB::transaction(function () use ($data) {
                // Limpar dados
                if (!empty($data['cpf'])) {
                    $data['cpf'] = preg_replace('/\D/', '', $data['cpf']);
                }
                
                if (!empty($data['phone'])) {
                    $data['phone'] = preg_replace('/\D/', '', $data['phone']);
                }

                $guardian = Guardian::create($data);

                // Limpar cache relacionado
                $this->forgetCacheKey('stats');
                $this->forgetCacheKey('search_*');

                return $guardian;
            });
        }, ['guardian_name' => $data['name'] ?? 'N/A']);
    }

    /**
     * Atualizar responsável
     */
    public function updateGuardian(Guardian $guardian, array $data): Guardian
    {
        return $this->executeWithLog('update_guardian', function () use ($guardian, $data) {
            return DB::transaction(function () use ($guardian, $data) {
                // Limpar dados
                if (!empty($data['cpf'])) {
                    $data['cpf'] = preg_replace('/\D/', '', $data['cpf']);
                }
                
                if (!empty($data['phone'])) {
                    $data['phone'] = preg_replace('/\D/', '', $data['phone']);
                }

                $guardian->update($data);

                // Limpar cache relacionado
                $this->forgetCacheKey('stats');
                $this->forgetCacheKey('search_*');

                return $guardian->fresh();
            });
        }, ['guardian_id' => $guardian->id]);
    }

    /**
     * Remover responsável
     */
    public function deleteGuardian(Guardian $guardian): bool
    {
        return $this->executeWithLog('delete_guardian', function () use ($guardian) {
            return DB::transaction(function () use ($guardian) {
                // Verificar se há alunos vinculados
                if ($guardian->students()->count() > 0) {
                    throw new \Exception('Não é possível excluir um responsável que possui alunos vinculados.');
                }

                $result = $guardian->delete();

                // Limpar cache relacionado
                $this->forgetCacheKey('stats');

                return $result;
            });
        }, ['guardian_id' => $guardian->id]);
    }

    /**
     * Buscar responsáveis para API (autocomplete)
     */
    public function searchGuardiansForApi(string $search, int $limit = 10, bool $includeStudentsCount = false)
    {
        $cacheKey = 'search_' . md5($search . $limit . ($includeStudentsCount ? '1' : '0'));
        
        return $this->executeWithCache($cacheKey, function () use ($search, $limit, $includeStudentsCount) {
            
            // ✅ Use o scope explicitamente
            return Guardian::search($search) // Seu scope atual
                ->select('id', 'name', 'phone', 'email', 'profession')
                ->orderBy('name') // Melhora a UX
                ->limit($limit)
                ->get()
                ->map(function ($guardian) use ($includeStudentsCount) {
                    $data = [
                        'id' => $guardian->id,
                        'name' => $guardian->name,
                        'phone' => $guardian->formatted_phone,
                        'email' => $guardian->email,
                        'profession' => $guardian->profession,
                    ];
                    
                    if ($includeStudentsCount) {
                        $data['students_count'] = $guardian->students()->count();
                    }
                    
                    return $data;
                });
        }, 30);
    }

    /**
     * Obter estatísticas dos responsáveis
     */
    public function getGuardiansStats(): array
    {
        $cacheKey = 'stats';
        
        return $this->executeWithCache($cacheKey, function () {
            return [
                'total' => Guardian::count(),
                'with_students' => Guardian::has('students')->count(),
                'primary_guardians' => Guardian::whereHas('students', function ($q) {
                    $q->wherePivot('is_primary', true);
                })->count(),
                'financial_guardians' => Guardian::whereHas('students', function ($q) {
                    $q->wherePivot('is_financial', true);
                })->count(),
                'new_this_month' => Guardian::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'by_profession' => Guardian::selectRaw('profession, COUNT(*) as count')
                    ->whereNotNull('profession')
                    ->groupBy('profession')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->pluck('count', 'profession')
                    ->toArray(),
            ];
        }, 60);
    }

    /**
     * Vincular responsável a aluno
     */
    public function attachToStudent(Guardian $guardian, Student $student, array $relationshipData): void
    {
        $this->executeWithLog('attach_student', function () use ($guardian, $student, $relationshipData) {
            return DB::transaction(function () use ($guardian, $student, $relationshipData) {
                // Verificar se já não está vinculado
                if ($guardian->students()->where('student_id', $student->id)->exists()) {
                    throw new \Exception('Este responsável já está vinculado ao aluno.');
                }

                $guardian->students()->attach($student->id, [
                    'relationship' => $relationshipData['relationship'],
                    'is_primary' => $relationshipData['is_primary'] ?? false,
                    'is_financial' => $relationshipData['is_financial'] ?? false,
                    'can_pickup' => $relationshipData['can_pickup'] ?? true,
                ]);

                // Se for responsável principal, remover flag dos outros
                if ($relationshipData['is_primary'] ?? false) {
                    $student->guardians()
                        ->where('guardian_id', '!=', $guardian->id)
                        ->updateExistingPivot($student->id, ['is_primary' => false]);
                }
            });
        }, [
            'guardian_id' => $guardian->id,
            'student_id' => $student->id,
            'relationship' => $relationshipData['relationship'] ?? 'N/A'
        ]);
    }

    /**
     * Desvincular responsável de aluno
     */
    public function detachFromStudent(Guardian $guardian, Student $student): void
    {
        $this->executeWithLog('detach_student', function () use ($guardian, $student) {
            return DB::transaction(function () use ($guardian, $student) {
                // Verificar se não é o único responsável
                if ($student->guardians()->count() <= 1) {
                    throw new \Exception('Não é possível remover o único responsável do aluno.');
                }

                $guardian->students()->detach($student->id);

                // Se era o responsável principal, definir outro como principal
                if (!$student->guardians()->wherePivot('is_primary', true)->exists()) {
                    $firstGuardian = $student->guardians()->first();
                    if ($firstGuardian) {
                        $student->guardians()->updateExistingPivot($firstGuardian->id, ['is_primary' => true]);
                    }
                }
            });
        }, [
            'guardian_id' => $guardian->id,
            'student_id' => $student->id
        ]);
    }

    /**
     * Atualizar relacionamento com aluno
     */
    public function updateStudentRelationship(Guardian $guardian, Student $student, array $relationshipData): void
    {
        $this->executeWithLog('update_relationship', function () use ($guardian, $student, $relationshipData) {
            return DB::transaction(function () use ($guardian, $student, $relationshipData) {
                $guardian->students()->updateExistingPivot($student->id, [
                    'relationship' => $relationshipData['relationship'],
                    'is_primary' => $relationshipData['is_primary'] ?? false,
                    'is_financial' => $relationshipData['is_financial'] ?? false,
                    'can_pickup' => $relationshipData['can_pickup'] ?? true,
                ]);

                // Se for responsável principal, remover flag dos outros
                if ($relationshipData['is_primary'] ?? false) {
                    $student->guardians()
                        ->where('guardian_id', '!=', $guardian->id)
                        ->updateExistingPivot($student->id, ['is_primary' => false]);
                }
            });
        }, [
            'guardian_id' => $guardian->id,
            'student_id' => $student->id
        ]);
    }

    /**
     * Obter responsáveis de um aluno
     */
    public function getStudentGuardians(Student $student): \Illuminate\Database\Eloquent\Collection
    {
        return $student->guardians()
            ->withPivot(['relationship', 'is_primary', 'is_financial', 'can_pickup'])
            ->orderByPivot('is_primary', 'desc')
            ->get();
    }

    /**
     * Obter alunos de um responsável
     */
    public function getGuardianStudents(Guardian $guardian): \Illuminate\Database\Eloquent\Collection
    {
        return $guardian->students()
            ->withPivot(['relationship', 'is_primary', 'is_financial', 'can_pickup'])
            ->with('activeEnrollment.classroom')
            ->orderBy('name')
            ->get();
    }

    /**
     * Buscar responsáveis por critérios específicos
     */
    public function findGuardiansByCriteria(array $criteria): \Illuminate\Database\Eloquent\Collection
    {
        $query = Guardian::query();

        if (!empty($criteria['has_primary_students'])) {
            $query->whereHas('students', function ($q) {
                $q->wherePivot('is_primary', true);
            });
        }

        if (!empty($criteria['has_financial_responsibility'])) {
            $query->whereHas('students', function ($q) {
                $q->wherePivot('is_financial', true);
            });
        }

        if (!empty($criteria['can_pickup'])) {
            $query->whereHas('students', function ($q) {
                $q->wherePivot('can_pickup', true);
            });
        }

        if (!empty($criteria['profession'])) {
            $query->where('profession', 'like', "%{$criteria['profession']}%");
        }

        if (!empty($criteria['city'])) {
            $query->whereJsonContains('address->city', $criteria['city']);
        }

        return $query->with('students')->get();
    }

    /**
     * Validar dados do responsável
     */
    public function validateGuardianData(array $data, ?Guardian $guardian = null): array
    {
        $errors = [];

        // Verificar CPF único
        if (!empty($data['cpf'])) {
            $query = Guardian::where('cpf', preg_replace('/\D/', '', $data['cpf']));
            if ($guardian) {
                $query->where('id', '!=', $guardian->id);
            }
            if ($query->exists()) {
                $errors['cpf'] = 'Este CPF já está cadastrado.';
            }
        }

        // Verificar email único
        if (!empty($data['email'])) {
            $query = Guardian::where('email', $data['email']);
            if ($guardian) {
                $query->where('id', '!=', $guardian->id);
            }
            if ($query->exists()) {
                $errors['email'] = 'Este email já está cadastrado.';
            }
        }

        return $errors;
    }

    /**
     * Gerar relatório de responsáveis
     */
    public function generateGuardiansReport(array $filters = []): array
    {
        return $this->executeWithLog('generate_report', function () use ($filters) {
            $query = Guardian::with(['students' => function ($q) {
                $q->with('activeEnrollment.classroom');
            }]);

            // Aplicar filtros
            if (!empty($filters['profession'])) {
                $query->where('profession', $filters['profession']);
            }

            if (!empty($filters['has_students'])) {
                $query->has('students');
            }

            $guardians = $query->get();

            return [
                'guardians' => $guardians,
                'summary' => [
                    'total' => $guardians->count(),
                    'with_students' => $guardians->filter(fn($g) => $g->students->count() > 0)->count(),
                    'primary_guardians' => $guardians->filter(function ($guardian) {
                        return $guardian->students->contains(function ($student) {
                            return $student->pivot->is_primary;
                        });
                    })->count(),
                    'by_profession' => $guardians->groupBy('profession')->map->count(),
                    'average_students_per_guardian' => $guardians->avg(fn($g) => $g->students->count()),
                ],
            ];
        });
    }
}