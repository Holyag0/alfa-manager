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
    
    // Rotas de Finanças
    Route::prefix('financial')->name('financial.')->group(function () {
        // Transações
        Route::get('/transactions', [App\Http\Controllers\Financial\FinancialTransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/create', [App\Http\Controllers\Financial\FinancialTransactionController::class, 'create'])->name('transactions.create');
        Route::post('/transactions', [App\Http\Controllers\Financial\FinancialTransactionController::class, 'store'])->name('transactions.store');
        Route::get('/transactions/{id}/details', [App\Http\Controllers\Financial\FinancialTransactionController::class, 'details'])->name('transactions.details');
        Route::get('/transactions/{id}/edit', [App\Http\Controllers\Financial\FinancialTransactionController::class, 'edit'])->name('transactions.edit');
        Route::put('/transactions/{id}', [App\Http\Controllers\Financial\FinancialTransactionController::class, 'update'])->name('transactions.update');
        Route::patch('/transactions/{id}/status', [App\Http\Controllers\Financial\FinancialTransactionController::class, 'updateStatus'])->name('transactions.updateStatus');
        Route::get('/transactions/{id}', [App\Http\Controllers\Financial\FinancialTransactionController::class, 'show'])->name('transactions.show');
        Route::post('/transactions/{id}/cancel', [App\Http\Controllers\Financial\FinancialTransactionController::class, 'cancel'])->name('transactions.cancel');
        Route::delete('/transactions/{id}', [App\Http\Controllers\Financial\FinancialTransactionController::class, 'destroy'])->name('transactions.destroy');
        
        // Relatório
        Route::get('/report', [App\Http\Controllers\Financial\FinancialTransactionController::class, 'report'])->name('report');
        
        // Categorias
        Route::get('/categories', [App\Http\Controllers\Financial\FinancialCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [App\Http\Controllers\Financial\FinancialCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{id}', [App\Http\Controllers\Financial\FinancialCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [App\Http\Controllers\Financial\FinancialCategoryController::class, 'destroy'])->name('categories.destroy');
        
        // Fornecedores/Pagadores
        Route::get('/suppliers', [App\Http\Controllers\Financial\SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/suppliers/create', [App\Http\Controllers\Financial\SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/suppliers', [App\Http\Controllers\Financial\SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/suppliers/{id}', [App\Http\Controllers\Financial\SupplierController::class, 'show'])->name('suppliers.show');
        Route::get('/suppliers/{id}/edit', [App\Http\Controllers\Financial\SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/suppliers/{id}', [App\Http\Controllers\Financial\SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{id}', [App\Http\Controllers\Financial\SupplierController::class, 'destroy'])->name('suppliers.destroy');
        Route::get('/suppliers-api', [App\Http\Controllers\Financial\SupplierController::class, 'api'])->name('suppliers.api');
    });
});
