<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\GuardianService;
use Illuminate\Support\Facades\Log;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// SCHOOL MANAGEMENT API ROUTES
Route::middleware('auth:sanctum')->group(function () {
    // BUSCA E AUTOCOMPLETE
    // Buscar alunos
    Route::get('students/search', function (Request $request) {
        $students = \App\Models\Student::search($request->get('q', ''))
            ->with('activeEnrollment.classroom')
            ->select('id', 'name', 'registration_number', 'status')
            ->limit($request->get('limit', 10))
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
        
        return response()->json($students);
    })->name('api.students.search');
    // Buscar turmas
    Route::get('classrooms/search', function (Request $request) {
        $classrooms = \App\Models\Classroom::search($request->get('q', ''))
            ->active()
            ->currentYear()
            ->with('teacher')
            ->select('id', 'name', 'grade_level', 'section', 'current_students', 'max_students', 'teacher_id')
            ->limit($request->get('limit', 10))
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
        
        return response()->json($classrooms);
    })->name('api.classrooms.search');
    // Buscar responsáveis
    Route::get('guardians/search', function (Request $request, GuardianService $service) {
        dd('oi');
    $request->validate([
        'q' => 'required|string|min:2|max:100',
        'limit' => 'integer|min:1|max:50'
    ]);

    try {
        $guardians = $service->searchGuardiansForApi(
            search: $request->get('q'),
            limit: $request->get('limit', 10),
            includeStudentsCount: false // Para performance
        );

        return response()->json([
            'success' => true,
            'data' => $guardians,
            'count' => $guardians->count(),
            'search_term' => $request->get('q')
        ]);

    } catch (\Exception $e) {
        Log::error('Guardian search API error:', [
            'search_term' => $request->get('q'),
            'error' => $e->getMessage(),
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Erro interno na busca',
            'data' => []
        ], 500);
    }
})->name('api.guardians.search');
    // DASHBOARD E ESTATÍSTICAS
    // Dados para dashboard
    Route::get('dashboard/stats', function () {
        $currentYear = now()->year;
        
        return response()->json([
            'students' => [
                'total' => \App\Models\Student::count(),
                'active' => \App\Models\Student::active()->count(),
                'inactive' => \App\Models\Student::where('status', 'inactive')->count(),
                'new_this_month' => \App\Models\Student::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ],
            'classrooms' => [
                'total' => \App\Models\Classroom::where('school_year', $currentYear)->count(),
                'active' => \App\Models\Classroom::where('school_year', $currentYear)->active()->count(),
                'with_spots' => \App\Models\Classroom::where('school_year', $currentYear)
                    ->withAvailableSpots()->count(),
                'occupancy_rate' => \App\Models\Classroom::where('school_year', $currentYear)
                    ->selectRaw('ROUND((SUM(current_students) / SUM(max_students)) * 100, 1) as rate')
                    ->value('rate') ?? 0,
            ],
            'enrollments' => [
                'active' => \App\Models\Enrollment::active()->count(),
                'this_month' => \App\Models\Enrollment::whereMonth('enrollment_date', now()->month)
                    ->whereYear('enrollment_date', now()->year)
                    ->count(),
            ],
            'guardians' => [
                'total' => \App\Models\Guardian::count(),
                'with_students' => \App\Models\Guardian::has('students')->count(),
            ],
        ]);
    })->name('api.dashboard.stats');

    // Estatísticas detalhadas dos alunos
    Route::get('students/stats', function (Request $request) {
        $year = $request->get('year', now()->year);
        
        return response()->json([
            'by_status' => \App\Models\Student::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            'by_grade' => \App\Models\Student::whereHas('activeEnrollment.classroom', function ($q) use ($year) {
                    $q->where('school_year', $year);
                })
                ->with('activeEnrollment.classroom')
                ->get()
                ->groupBy('activeEnrollment.classroom.grade_level')
                ->map->count(),
            'by_month' => \App\Models\Student::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', $year)
                ->groupBy('month')
                ->pluck('count', 'month'),
        ]);
    })->name('api.students.stats');

    // Estatísticas das turmas
    Route::get('classrooms/stats', function (Request $request) {
        $year = $request->get('year', now()->year);
        
        return response()->json([
            'occupancy' => \App\Models\Classroom::where('school_year', $year)
                ->select('name', 'current_students', 'max_students')
                ->get()
                ->map(function ($classroom) {
                    return [
                        'name' => $classroom->name,
                        'occupancy_rate' => $classroom->max_students > 0 
                            ? round(($classroom->current_students / $classroom->max_students) * 100, 1) 
                            : 0,
                    ];
                }),
            'by_grade' => \App\Models\Classroom::where('school_year', $year)
                ->selectRaw('grade_level, COUNT(*) as count, SUM(current_students) as students')
                ->groupBy('grade_level')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->grade_level => [
                        'classrooms' => $item->count,
                        'students' => $item->students,
                    ]];
                }),
        ]);
    })->name('api.classrooms.stats');
    // INFORMAÇÕES ESPECÍFICAS
    // Verificar disponibilidade de turma
    Route::get('classrooms/{classroom}/availability', function (\App\Models\Classroom $classroom) {
        return response()->json([
            'can_enroll' => $classroom->canEnrollStudent(),
            'available_spots' => $classroom->available_spots,
            'current_students' => $classroom->current_students,
            'max_students' => $classroom->max_students,
            'occupancy_percentage' => $classroom->occupancy_percentage,
            'is_full' => $classroom->is_full,
        ]);
    })->name('api.classrooms.availability');

    // Informações do aluno para API
    Route::get('students/{student}', function (\App\Models\Student $student) {
        $student->load(['guardians', 'activeEnrollment.classroom']);
        
        return response()->json([
            'id' => $student->id,
            'name' => $student->name,
            'registration_number' => $student->formatted_registration_number,
            'status' => $student->status,
            'age' => $student->age,
            'current_classroom' => $student->current_classroom,
            'primary_guardian' => $student->getPrimaryGuardian()?->name,
            'photo_url' => $student->photo_url,
        ]);
    })->name('api.students.show');
    // VALIDAÇÕES E VERIFICAÇÕES
    
    // Verificar se CPF já existe
    Route::post('students/check-cpf', function (Request $request) {
        $cpf = preg_replace('/\D/', '', $request->get('cpf'));
        $studentId = $request->get('student_id');
        
        $query = \App\Models\Student::where('cpf', $cpf);
        if ($studentId) {
            $query->where('id', '!=', $studentId);
        }
        
        return response()->json([
            'exists' => $query->exists(),
            'message' => $query->exists() ? 'CPF já está em uso' : 'CPF disponível',
        ]);
    })->name('api.students.check-cpf');

    // Verificar se email já existe
    Route::post('students/check-email', function (Request $request) {
        $email = $request->get('email');
        $studentId = $request->get('student_id');
        
        $query = \App\Models\Student::where('email', $email);
        if ($studentId) {
            $query->where('id', '!=', $studentId);
        }
        
        return response()->json([
            'exists' => $query->exists(),
            'message' => $query->exists() ? 'Email já está em uso' : 'Email disponível',
        ]);
    })->name('api.students.check-email');

    // Verificar conflitos de matrícula
    Route::post('enrollments/check-conflicts', function (Request $request) {
        $studentId = $request->get('student_id');
        $classroomId = $request->get('classroom_id');
        
        $conflicts = [];
        
        $student = \App\Models\Student::find($studentId);
        $classroom = \App\Models\Classroom::find($classroomId);
        
        if (!$student) {
            $conflicts[] = 'Aluno não encontrado';
        } elseif ($student->activeEnrollment) {
            $conflicts[] = 'Aluno já possui matrícula ativa';
        }
        
        if (!$classroom) {
            $conflicts[] = 'Turma não encontrada';
        } elseif (!$classroom->canEnrollStudent()) {
            $conflicts[] = 'Turma está lotada';
        }
        
        return response()->json([
            'has_conflicts' => !empty($conflicts),
            'conflicts' => $conflicts,
        ]);
    })->name('api.enrollments.check-conflicts');
});