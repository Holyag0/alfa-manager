<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('user', App\Http\Controllers\Cadastros\UserController::class);
    Route::prefix('usuario')->controller(App\Http\Controllers\Cadastros\UserController::class)
        ->group(function () {
            Route::get('/', function () {
                return redirect()->route('user.index');
            });
            Route::get('atribuir-cargo-{id}', 'atribuirRole')->name('atribuir.cargo');
            Route::post('atribuindo-cargo', 'storeRoleToUser')->name('atribuindo-cargo.store');
            Route::get('desatribuir-cargo-{id}', 'desatribuirRole')->name('desatribuir.cargo');
            Route::post('desatribuindo-cargo', 'removeRoleFromUser')->name('desatribuir.cargo.store');
        });
    Route::middleware('permission:manage-auth')->group(function () {
        Route::get(
            '/admin-autorizacao',
            [App\Http\Controllers\UserManagementController::class, 'index']
        )
            ->name('admin.users.index');
        Route::patch(
            '/admin/users/{user}/toggle-auth',
            [App\Http\Controllers\UserManagementController::class, 'toggleAuth']
        )
            ->name('admin.users.toggle-auth');
    });
    Route::prefix('admin')->name('admin.')->group(function () {
        // ROTAS DE ALUNOS
        Route::resource('students', App\Http\Controllers\Admin\StudentController::class);
        // Rotas extras para alunos
        Route::get('/', function () {
            return redirect()->route('admin.students.index');
        });
        Route::prefix('students')->name('students.')->group(function () {
            Route::patch(
                '{student}/toggle-status',
                [App\Http\Controllers\Admin\StudentController::class, 'toggleStatus']
            )
                ->name('toggle-status');
            Route::patch(
                '{student}/transfer',
                [App\Http\Controllers\Admin\StudentController::class, 'transfer']
            )
                ->name('transfer');
        });
        // ROTAS DE TURMAS
        Route::resource(
            'classrooms',
            App\Http\Controllers\Admin\ClassroomController::class
        );

        // Rotas extras para turmas
        Route::prefix('classrooms')->name('classrooms.')->group(function () {
            Route::post(
                '{classroom}/enroll',
                [App\Http\Controllers\Admin\ClassroomController::class, 'enrollStudent']
            )
                ->name('enroll');

            Route::delete(
                '{classroom}/students/{student}',
                [App\Http\Controllers\Admin\ClassroomController::class, 'removeStudent']
            )
                ->name('remove-student');

            Route::post(
                '{classroom}/duplicate',
                [App\Http\Controllers\Admin\ClassroomController::class, 'duplicate']
            )
                ->name('duplicate');

            Route::get(
                '{classroom}/report',
                [App\Http\Controllers\Admin\ClassroomController::class, 'report']
            )
                ->name('report');

            Route::patch(
                '{classroom}/update-count',
                [App\Http\Controllers\Admin\ClassroomController::class, 'updateStudentCount']
            )
                ->name('update-count');
        });
        // ROTAS DE RELATÓRIOS GERAIS
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('students', function () {
                return Inertia::render('Admin/Reports/Students');
            })->name('students');

            Route::get('classrooms', function () {
                return Inertia::render('Admin/Reports/Classrooms');
            })->name('classrooms');

            Route::get('enrollments', function () {
                return Inertia::render('Admin/Reports/Enrollments');
            })->name('enrollments');
        });
    });
    // ROTAS PARA UPLOADS
    Route::post('upload/student-photo', function (Request $request) {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $path = $request->file('photo')->store('students/photos', 'public');

        return response()->json([
            'path' => $path,
            'url' => asset('storage/' . $path)
        ]);
    })->name('upload.student-photo');
    // ROTAS DE EXPORTAÇÃO
    Route::prefix('export')->name('export.')->group(function () {
        // Exportar lista de alunos
        Route::get('students', function (Request $request) {
            $students = \App\Models\Student::with(['guardians', 'activeEnrollment.classroom'])
                ->when($request->classroom_id, function ($q) use ($request) {
                    $q->byClassroom($request->classroom_id);
                })
                ->when($request->status, function ($q) use ($request) {
                    $q->where('status', $request->status);
                })
                ->orderBy('name')
                ->get();

            return response()->json($students);
        })->name('students');
        // Exportar lista de turmas
        Route::get('classrooms', function (Request $request) {
            $classrooms = \App\Models\Classroom::with(['teacher', 'students'])
                ->when($request->year, function ($q) use ($request) {
                    $q->where('school_year', $request->year);
                }, function ($q) {
                    $q->currentYear();
                })
                ->orderBy('grade_level')
                ->orderBy('section')
                ->get();

            return response()->json($classrooms);
        })->name('classrooms');
        // Exportar ficha do aluno
        Route::get('students/{student}/profile', function (\App\Models\Student $student) {
            $student->load([
                'guardians',
                'enrollments.classroom.teacher',
                'activeEnrollment.classroom.teacher'
            ]);
            return response()->json($student);
        })->name('student.profile');
    });
    Route::resource('roles', App\Http\Controllers\Cadastros\RoleController::class);
    Route::resource('permissions', App\Http\Controllers\Cadastros\PermissionController::class);
});
