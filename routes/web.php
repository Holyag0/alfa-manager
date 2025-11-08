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

// Rota para gestão de turmas
Route::get('/turmas', function () {
    return Inertia::render('Turmas/Index');
})->name('turmas.index');

Route::get('/turmas/{classroom}/edit', function ($classroom) {
    return Inertia::render('Turmas/Edit', [
        'classroomId' => (int) $classroom,
    ]);
})->name('turmas.edit');
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
    Route::resource('categories', App\Http\Controllers\Cadastros\CategoryController::class);
    
    // Rotas adicionais para categorias
    Route::prefix('categories')->name('categories.')->controller(App\Http\Controllers\Cadastros\CategoryController::class)
        ->group(function () {
            Route::get('{category}/toggle-status', 'toggleStatus')->name('toggle-status');
        });
    
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
            Route::post('{id}/renovar', 'renew')->name('renovar');
            Route::get('aluno/{studentId}/historico', 'history')->name('historico');
            Route::get('ano/{year}', 'byYear')->name('por-ano');
            // Rotas do Wizard de Matrícula
            Route::post('wizard/store', 'wizardStore')->name('wizard.store');
            Route::post('wizard/complete', 'wizardComplete')->name('wizard.complete');
            Route::post('wizard/reset', 'wizardReset')->name('wizard.reset');
            // Rota do Wizard Simplificado
            Route::get('create-simple', 'createSimple')->name('create-simple');
        });
    
    // Rotas para gerenciar vínculos entre responsáveis e alunos
    Route::prefix('students/{student}/guardians')->name('students.guardians.')->controller(App\Http\Controllers\Matriculas\GuardianController::class)
        ->group(function () {
            Route::get('search-not-linked', 'searchNotLinked')->name('search-not-linked');
            Route::post('attach', 'attachToStudent')->name('attach');
            Route::delete('{guardian}', 'detachFromStudent')->name('detach');
        });
    
    // Rotas CRUD`s do módulo Comercial
    Route::resource('services', App\Http\Controllers\Comercial\ServiceController::class);
    Route::resource('packages', App\Http\Controllers\Comercial\PackageController::class);
    
    // Rotas adicionais do módulo Comercial
    Route::prefix('comercial')->name('comercial.')->group(function () {
        Route::get('/', function () {
            return redirect()->route('services.index');
        });
        
        // Rotas específicas para serviços
        Route::prefix('services')->name('services.')->controller(App\Http\Controllers\Comercial\ServiceController::class)
            ->group(function () {
                Route::get('{service}/toggle-status', 'toggleStatus')->name('toggle-status');
                Route::get('categories/list', 'getCategories')->name('categories.list');
                Route::get('api', 'getServicesApi')->name('api');
            });
        
        // Rotas específicas para pacotes
        Route::prefix('packages')->name('packages.')->controller(App\Http\Controllers\Comercial\PackageController::class)
            ->group(function () {
                Route::get('{package}/toggle-status', 'toggleStatus')->name('toggle-status');
                Route::get('{package}/services', 'getPackageServices')->name('services');
                Route::post('{package}/services/{service}', 'addServiceToPackage')->name('add-service');
                Route::delete('{package}/services/{service}', 'removeServiceFromPackage')->name('remove-service');
                Route::get('categories/list', 'getCategories')->name('categories.list');
            });
    });
});
