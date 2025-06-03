<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class EnrollmentService extends BaseService
{
    protected string $cachePrefix = 'enrollment_service';

    /**
     * Listar matrículas com filtros
     */
    public function getEnrollmentsList(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->executeWithLog('list_enrollments', function () use ($filters, $perPage) {
            $query = Enrollment::with(['student', 'classroom.teacher'])
                ->orderBy('enrollment_date', 'desc');

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['classroom_id'])) {
                $query->byClassroom($filters['classroom_id']);
            }

            if (!empty($filters['student_id'])) {
                $query->byStudent($filters['student_id']);
            }

            if (!empty($filters['year'])) {
                $query->whereYear('enrollment_date', $filters['year']);
            } else {
                $query->currentYear();
            }

            if (!empty($filters['grade'])) {
                $query->whereHas('classroom', function ($q) use ($filters) {
                    $q->where('grade_level', $filters['grade']);
                });
            }

            return $query->paginate($perPage)->withQueryString();
        });
    }

    /**
     * Buscar matrícula com relacionamentos
     */
    public function findEnrollmentWithRelations(int $enrollmentId): Enrollment
    {
        return $this->executeWithLog('find_enrollment', function () use ($enrollmentId) {
            return Enrollment::with([
                'student.guardians',
                'classroom.teacher'
            ])->findOrFail($enrollmentId);
        }, ['enrollment_id' => $enrollmentId]);
    }

    /**
     * Criar nova matrícula
     */
    public function createEnrollment(array $data): Enrollment
    {
        return $this->executeWithLog('create_enrollment', function () use ($data) {
            return DB::transaction(function () use ($data) {
                $student = Student::findOrFail($data['student_id']);
                $classroom = Classroom::findOrFail($data['classroom_id']);

                // Verificar se a turma pode receber o aluno
                if (!$classroom->canEnrollStudent()) {
                    throw new \Exception('A turma está lotada.');
                }

                // Verificar se o aluno já está matriculado em outra turma
                if ($student->activeEnrollment) {
                    throw new \Exception('O aluno já está matriculado em outra turma ativa.');
                }

                // Criar matrícula
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'classroom_id' => $classroom->id,
                    'enrollment_date' => $data['enrollment_date'] ?? now()->toDateString(),
                    'status' => 'active',
                    'notes' => $data['notes'] ?? null,
                ]);

                // Atualizar contador da turma
                $classroom->increment('current_students');

                // Limpar caches relacionados
                $this->forgetCacheKey('stats_*');
                $this->forgetCacheKey('classroom_' . $classroom->id);

                return $enrollment;
            });
        }, [
            'student_id' => $data['student_id'],
            'classroom_id' => $data['classroom_id']
        ]);
    }

    /**
     * Atualizar matrícula
     */
    public function updateEnrollment(Enrollment $enrollment, array $data): Enrollment
    {
        return $this->executeWithLog('update_enrollment', function () use ($enrollment, $data) {
            return DB::transaction(function () use ($enrollment, $data) {
                // Se mudou de turma
                if (!empty($data['classroom_id']) && $data['classroom_id'] != $enrollment->classroom_id) {
                    $newClassroom = Classroom::findOrFail($data['classroom_id']);
                    
                    if (!$newClassroom->canEnrollStudent()) {
                        throw new \Exception('A nova turma está lotada.');
                    }

                    // Atualizar contadores
                    $enrollment->classroom->decrement('current_students');
                    $newClassroom->increment('current_students');
                }

                $enrollment->update($data);

                // Limpar caches
                $this->forgetCacheKey('stats_*');

                return $enrollment->fresh();
            });
        }, ['enrollment_id' => $enrollment->id]);
    }

    /**
     * Finalizar matrícula
     */
    public function finishEnrollment(Enrollment $enrollment, string $reason = 'completed', ?string $endDate = null): bool
    {
        return $this->executeWithLog('finish_enrollment', function () use ($enrollment, $reason, $endDate) {
            return DB::transaction(function () use ($enrollment, $reason, $endDate) {
                $success = $enrollment->finish($reason, $endDate);

                if ($success) {
                    // Limpar caches
                    $this->forgetCacheKey('stats_*');
                    $this->forgetCacheKey('classroom_' . $enrollment->classroom_id);
                }

                return $success;
            });
        }, [
            'enrollment_id' => $enrollment->id,
            'reason' => $reason
        ]);
    }

    /**
     * Reativar matrícula
     */
    public function reactivateEnrollment(Enrollment $enrollment): bool
    {
        return $this->executeWithLog('reactivate_enrollment', function () use ($enrollment) {
            return DB::transaction(function () use ($enrollment) {
                $success = $enrollment->reactivate();

                if ($success) {
                    // Limpar caches
                    $this->forgetCacheKey('stats_*');
                    $this->forgetCacheKey('classroom_' . $enrollment->classroom_id);
                }

                return $success;
            });
        }, ['enrollment_id' => $enrollment->id]);
    }

    /**
     * Transferir aluno (criar nova matrícula e finalizar a atual)
     */
    public function transferStudent(Student $student, Classroom $newClassroom, ?string $transferDate = null, ?string $notes = null): Enrollment
    {
        return $this->executeWithLog('transfer_student', function () use ($student, $newClassroom, $transferDate, $notes) {
            return DB::transaction(function () use ($student, $newClassroom, $transferDate, $notes) {
                $currentEnrollment = $student->activeEnrollment;
                
                if (!$currentEnrollment) {
                    throw new \Exception('Aluno não possui matrícula ativa.');
                }

                if (!$newClassroom->canEnrollStudent()) {
                    throw new \Exception('A turma de destino está lotada.');
                }

                $transferDate = $transferDate ?? now()->toDateString();

                // Finalizar matrícula atual
                $currentEnrollment->finish('transferred', $transferDate);

                // Criar nova matrícula
                $newEnrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'classroom_id' => $newClassroom->id,
                    'enrollment_date' => $transferDate,
                    'status' => 'active',
                    'notes' => $notes ?? 'Transferido da turma ' . $currentEnrollment->classroom->name,
                ]);

                // Atualizar contadores
                $newClassroom->increment('current_students');

                // Limpar caches
                $this->forgetCacheKey('stats_*');

                return $newEnrollment;
            });
        }, [
            'student_id' => $student->id,
            'from_classroom' => $student->activeEnrollment?->classroom->name,
            'to_classroom' => $newClassroom->name
        ]);
    }

    /**
     * Obter estatísticas de matrículas
     */
    public function getEnrollmentsStats(?int $year = null): array
    {
        $year = $year ?? now()->year;
        $cacheKey = 'stats_' . $year;
        
        return $this->executeWithCache($cacheKey, function () use ($year) {
            $baseQuery = Enrollment::whereYear('enrollment_date', $year);

            return [
                'total' => $baseQuery->count(),
                'active' => $baseQuery->where('status', 'active')->count(),
                'transferred' => $baseQuery->where('status', 'transferred')->count(),
                'completed' => $baseQuery->where('status', 'completed')->count(),
                'dropped' => $baseQuery->where('status', 'dropped')->count(),
                'this_month' => Enrollment::whereMonth('enrollment_date', now()->month)
                    ->whereYear('enrollment_date', now()->year)
                    ->count(),
                'by_month' => $baseQuery->selectRaw('MONTH(enrollment_date) as month, COUNT(*) as count')
                    ->groupBy('month')
                    ->orderBy('month')
                    ->pluck('count', 'month')
                    ->toArray(),
                'by_grade' => $baseQuery->with('classroom')
                    ->get()
                    ->groupBy('classroom.grade_level')
                    ->map->count()
                    ->toArray(),
                'average_duration' => $baseQuery->where('status', '!=', 'active')
                    ->whereNotNull('end_date')
                    ->selectRaw('AVG(DATEDIFF(end_date, enrollment_date)) as avg_days')
                    ->value('avg_days') ?? 0,
            ];
        }, 60);
    }

    /**
     * Obter histórico de matrículas de um aluno
     */
    public function getStudentEnrollmentHistory(Student $student): \Illuminate\Database\Eloquent\Collection
    {
        return $student->enrollments()
            ->with('classroom.teacher')
            ->orderBy('enrollment_date', 'desc')
            ->get();
    }

    /**
     * Obter matrículas de uma turma
     */
    public function getClassroomEnrollments(Classroom $classroom, ?string $status = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = $classroom->enrollments()->with('student.guardians');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('enrollment_date')->get();
    }

    /**
     * Obter relatório de matrículas por período
     */
    public function getEnrollmentReport(string $startDate, string $endDate, array $filters = []): array
    {
        return $this->executeWithLog('enrollment_report', function () use ($startDate, $endDate, $filters) {
            $query = Enrollment::with(['student', 'classroom.teacher'])
                ->whereBetween('enrollment_date', [$startDate, $endDate]);

            // Aplicar filtros adicionais
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['classroom_id'])) {
                $query->where('classroom_id', $filters['classroom_id']);
            }

            if (!empty($filters['grade'])) {
                $query->whereHas('classroom', function ($q) use ($filters) {
                    $q->where('grade_level', $filters['grade']);
                });
            }

            $enrollments = $query->orderBy('enrollment_date')->get();

            return [
                'period' => ['start' => $startDate, 'end' => $endDate],
                'filters' => $filters,
                'total_enrollments' => $enrollments->count(),
                'by_status' => $enrollments->groupBy('status')->map->count(),
                'by_classroom' => $enrollments->groupBy('classroom.name')->map->count(),
                'by_month' => $enrollments->groupBy(function ($enrollment) {
                    return $enrollment->enrollment_date->format('Y-m');
                })->map->count(),
                'by_grade' => $enrollments->groupBy('classroom.grade_level')->map->count(),
                'enrollments' => $enrollments,
                'summary' => [
                    'daily_average' => round($enrollments->count() / max(1, now()->parse($startDate)->diffInDays(now()->parse($endDate))), 2),
                    'most_popular_classroom' => $enrollments->groupBy('classroom.name')->sortByDesc->count()->keys()->first(),
                    'peak_month' => $enrollments->groupBy(function ($e) {
                        return $e->enrollment_date->format('Y-m');
                    })->sortByDesc->count()->keys()->first(),
                ],
            ];
        }, ['start_date' => $startDate, 'end_date' => $endDate]);
    }

    /**
     * Processar matrícula em lote
     */
    public function bulkEnroll(array $enrollmentData): array
    {
        return $this->executeWithLog('bulk_enrollment', function () use ($enrollmentData) {
            $results = ['success' => [], 'errors' => []];

            return DB::transaction(function () use ($enrollmentData, &$results) {
                foreach ($enrollmentData as $data) {
                    try {
                        $enrollment = $this->createEnrollment($data);
                        $results['success'][] = [
                            'enrollment_id' => $enrollment->id,
                            'student_name' => $enrollment->student->name,
                            'classroom_name' => $enrollment->classroom->name,
                        ];
                    } catch (\Exception $e) {
                        $results['errors'][] = [
                            'data' => $data,
                            'error' => $e->getMessage(),
                        ];
                    }
                }

                return $results;
            });
        });
    }

    /**
     * Verificar conflitos de matrícula
     */
    public function checkEnrollmentConflicts(int $studentId, int $classroomId): array
    {
        $conflicts = [];

        $student = Student::find($studentId);
        $classroom = Classroom::find($classroomId);

        if (!$student) {
            $conflicts[] = 'Aluno não encontrado.';
        }

        if (!$classroom) {
            $conflicts[] = 'Turma não encontrada.';
        }

        if ($student && $classroom) {
            // Verificar se aluno já está matriculado
            if ($student->activeEnrollment) {
                $conflicts[] = 'Aluno já possui matrícula ativa na turma ' . $student->activeEnrollment->classroom->name;
            }

            // Verificar capacidade da turma
            if (!$classroom->canEnrollStudent()) {
                $conflicts[] = 'Turma está lotada (' . $classroom->current_students . '/' . $classroom->max_students . ')';
            }

            // Verificar se é o mesmo ano escolar
            if ($classroom->school_year != now()->year) {
                $conflicts[] = 'Turma é de um ano letivo diferente (' . $classroom->school_year . ')';
            }
        }

        return $conflicts;
    }

    /**
     * Obter matrículas próximas ao vencimento
     */
    public function getExpiringEnrollments(int $daysAhead = 30): \Illuminate\Database\Eloquent\Collection
    {
        return $this->executeWithLog('expiring_enrollments', function () use ($daysAhead) {
            $endDate = now()->addDays($daysAhead);
            
            return Enrollment::active()
                ->with(['student', 'classroom'])
                ->where('enrollment_date', '<=', $endDate)
                ->orderBy('enrollment_date')
                ->get();
        });
    }

    /**
     * Calcular duração média das matrículas
     */
    public function calculateAverageEnrollmentDuration(?int $year = null): float
    {
        $year = $year ?? now()->year;
        $cacheKey = 'avg_duration_' . $year;

        return $this->executeWithCache($cacheKey, function () use ($year) {
            return Enrollment::whereYear('enrollment_date', $year)
                ->where('status', '!=', 'active')
                ->whereNotNull('end_date')
                ->selectRaw('AVG(DATEDIFF(end_date, enrollment_date)) as avg_days')
                ->value('avg_days') ?? 0;
        }, 120); // Cache por 2 horas
    }

    /**
     * Obter taxa de transferência por turma
     */
    public function getTransferRateByClassroom(?int $year = null): array
    {
        $year = $year ?? now()->year;
        $cacheKey = 'transfer_rate_' . $year;

        return $this->executeWithCache($cacheKey, function () use ($year) {
            $classrooms = Classroom::with(['enrollments' => function ($query) use ($year) {
                $query->whereYear('enrollment_date', $year);
            }])->where('school_year', $year)->get();

            return $classrooms->map(function ($classroom) {
                $totalEnrollments = $classroom->enrollments->count();
                $transfers = $classroom->enrollments->where('status', 'transferred')->count();
                
                return [
                    'classroom_id' => $classroom->id,
                    'classroom_name' => $classroom->name,
                    'total_enrollments' => $totalEnrollments,
                    'transfers' => $transfers,
                    'transfer_rate' => $totalEnrollments > 0 ? round(($transfers / $totalEnrollments) * 100, 2) : 0,
                ];
            })->sortByDesc('transfer_rate')->values()->toArray();
        }, 90);
    }

    /**
     * Validar dados de matrícula
     */
    public function validateEnrollmentData(array $data, ?Enrollment $enrollment = null): array
    {
        $errors = [];

        // Verificar se aluno existe e está ativo
        if (!empty($data['student_id'])) {
            $student = Student::find($data['student_id']);
            if (!$student) {
                $errors['student_id'] = 'Aluno não encontrado.';
            } elseif ($student->status !== 'active') {
                $errors['student_id'] = 'Aluno não está ativo.';
            }
        }

        // Verificar se turma existe e está ativa
        if (!empty($data['classroom_id'])) {
            $classroom = Classroom::find($data['classroom_id']);
            if (!$classroom) {
                $errors['classroom_id'] = 'Turma não encontrada.';
            } elseif ($classroom->status !== 'active') {
                $errors['classroom_id'] = 'Turma não está ativa.';
            } elseif (!$classroom->canEnrollStudent() && !$enrollment) {
                $errors['classroom_id'] = 'Turma está lotada.';
            }
        }

        // Verificar data de matrícula
        if (!empty($data['enrollment_date'])) {
            $enrollmentDate = \Carbon\Carbon::parse($data['enrollment_date']);
            if ($enrollmentDate->isFuture()) {
                $errors['enrollment_date'] = 'Data de matrícula não pode ser futura.';
            }
            if ($enrollmentDate->year < 2020) {
                $errors['enrollment_date'] = 'Data de matrícula muito antiga.';
            }
        }

        return $errors;
    }

    /**
     * Gerar certificado de matrícula
     */
    public function generateEnrollmentCertificate(Enrollment $enrollment): array
    {
        return $this->executeWithLog('generate_certificate', function () use ($enrollment) {
            $enrollment->load(['student.guardians', 'classroom.teacher']);

            return [
                'certificate_number' => 'CERT-' . $enrollment->id . '-' . now()->format('Y'),
                'student' => [
                    'name' => $enrollment->student->name,
                    'registration_number' => $enrollment->student->registration_number,
                    'birth_date' => $enrollment->student->birth_date->format('d/m/Y'),
                ],
                'classroom' => [
                    'name' => $enrollment->classroom->name,
                    'teacher' => $enrollment->classroom->teacher?->name,
                    'school_year' => $enrollment->classroom->school_year,
                ],
                'enrollment' => [
                    'date' => $enrollment->enrollment_date->format('d/m/Y'),
                    'status' => $enrollment->status,
                    'duration' => $enrollment->duration_in_days,
                ],
                'primary_guardian' => $enrollment->student->getPrimaryGuardian(),
                'generated_at' => now()->format('d/m/Y H:i:s'),
                'valid_until' => now()->addYear()->format('d/m/Y'),
            ];
        }, ['enrollment_id' => $enrollment->id]);
    }

    /**
     * Sincronizar contadores de turmas
     */
    public function syncClassroomCounters(): array
    {
        return $this->executeWithLog('sync_counters', function () {
            $results = ['updated' => 0, 'errors' => []];

            $classrooms = Classroom::all();

            foreach ($classrooms as $classroom) {
                try {
                    $actualCount = $classroom->activeEnrollments()->count();
                    
                    if ($classroom->current_students !== $actualCount) {
                        $classroom->update(['current_students' => $actualCount]);
                        $results['updated']++;
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'classroom_id' => $classroom->id,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            // Limpar cache após sincronização
            $this->clearServiceCache();

            return $results;
        });
    }

    /**
     * Obter estatísticas de retention (permanência)
     */
    public function getRetentionStats(?int $year = null): array
    {
        $year = $year ?? now()->year;
        $cacheKey = 'retention_stats_' . $year;

        return $this->executeWithCache($cacheKey, function () use ($year) {
            $enrollments = Enrollment::with('student')
                ->whereYear('enrollment_date', $year)
                ->get();

            $totalStudents = $enrollments->count();
            $completedYear = $enrollments->where('status', 'completed')->count();
            $stillActive = $enrollments->where('status', 'active')->count();
            $transferred = $enrollments->where('status', 'transferred')->count();
            $dropped = $enrollments->where('status', 'dropped')->count();

            return [
                'year' => $year,
                'total_enrollments' => $totalStudents,
                'completed_year' => $completedYear,
                'still_active' => $stillActive,
                'transferred_out' => $transferred,
                'dropped_out' => $dropped,
                'retention_rate' => $totalStudents > 0 ? round((($completedYear + $stillActive) / $totalStudents) * 100, 2) : 0,
                'completion_rate' => $totalStudents > 0 ? round(($completedYear / $totalStudents) * 100, 2) : 0,
                'dropout_rate' => $totalStudents > 0 ? round(($dropped / $totalStudents) * 100, 2) : 0,
                'transfer_rate' => $totalStudents > 0 ? round(($transferred / $totalStudents) * 100, 2) : 0,
            ];
        }, 120);
    }

    /**
     * Encontrar padrões de transferência
     */
    public function getTransferPatterns(?int $year = null): array
    {
        $year = $year ?? now()->year;

        return $this->executeWithLog('transfer_patterns', function () use ($year) {
            $transfers = Enrollment::with(['student', 'classroom'])
                ->where('status', 'transferred')
                ->whereYear('enrollment_date', $year)
                ->get();

            // Agrupar por turma de origem
            $fromClassrooms = $transfers->groupBy('classroom.name')
                ->map(function ($group) {
                    return [
                        'classroom' => $group->first()->classroom->name,
                        'transfers_count' => $group->count(),
                        'students' => $group->pluck('student.name')->toArray(),
                    ];
                })
                ->sortByDesc('transfers_count')
                ->values();

            // Encontrar turmas de destino (próximas matrículas dos mesmos alunos)
            $patterns = [];
            foreach ($transfers as $transfer) {
                $nextEnrollment = Enrollment::where('student_id', $transfer->student_id)
                    ->where('enrollment_date', '>', $transfer->end_date)
                    ->with('classroom')
                    ->first();

                if ($nextEnrollment) {
                    $key = $transfer->classroom->name . ' -> ' . $nextEnrollment->classroom->name;
                    $patterns[$key] = ($patterns[$key] ?? 0) + 1;
                }
            }

            return [
                'year' => $year,
                'total_transfers' => $transfers->count(),
                'from_classrooms' => $fromClassrooms->toArray(),
                'common_patterns' => collect($patterns)
                    ->sortByDesc(fn($count) => $count)
                    ->take(10)
                    ->toArray(),
                'peak_transfer_months' => $transfers->groupBy(function ($transfer) {
                    return $transfer->end_date?->format('Y-m') ?? 'N/A';
                })->map->count()->sortByDesc(fn($count) => $count)->toArray(),
            ];
        });
    }
}