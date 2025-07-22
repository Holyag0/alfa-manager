<?php
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
    // Rotas CRUD`s
    Route::resource('guardian',App\Http\Controllers\Matriculas\GuardianController::class);
    Route::resource('students', App\Http\Controllers\Matriculas\StudentController::class);
    Route::resource('user', App\Http\Controllers\Cadastros\UserController::class);
    Route::resource('matriculas', App\Http\Controllers\Matriculas\EnrollmentController::class);
    Route::resource('roles', App\Http\Controllers\Cadastros\RoleController::class);
    Route::resource('permissions', App\Http\Controllers\Cadastros\PermissionController::class);
    //rotas adicionais 
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
        Route::get('/admin-autorizacao', 
            [App\Http\Controllers\UserManagementController::class, 'index'])
            ->name('admin.users.index');
        Route::patch('/admin/users/{user}/toggle-auth', 
            [App\Http\Controllers\UserManagementController::class, 'toggleAuth'])
            ->name('admin.users.toggle-auth');
    });
    Route::prefix('matriculas')->name('matriculas.')->controller(App\Http\Controllers\Matriculas\EnrollmentController::class)
        ->group(function () {
            Route::post('{id}/cancelar', 'cancel')->name('cancelar');
            Route::post('{id}/trocar-turma', 'changeClassroom')->name('trocar-turma');
        });
    
    // Rotas para gerenciar vínculos entre responsáveis e alunos
    Route::prefix('students/{student}/guardians')->name('students.guardians.')->controller(App\Http\Controllers\Matriculas\GuardianController::class)
        ->group(function () {
            Route::get('search-not-linked', 'searchNotLinked')->name('search-not-linked');
            Route::post('attach', 'attachToStudent')->name('attach');
            Route::delete('{guardian}', 'detachFromStudent')->name('detach');
        });
});
