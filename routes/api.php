<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GuardianApiController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\ClassroomApiController;
use App\Http\Controllers\ClassroomLinkingController;
use App\Http\Controllers\Matriculas\GuardianController;
use App\Http\Controllers\Matriculas\StudentController;
use App\Http\Controllers\Api\MonthlyFeeController;
use App\Http\Controllers\Api\MonthlyFeeInstallmentController;
use App\Http\Controllers\Api\MonthlyFeePaymentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('classrooms', [ClassroomApiController::class, 'index']);
Route::get('classrooms/{classroom}', [ClassroomApiController::class, 'show']);
Route::put('classrooms/{classroom}', [ClassroomApiController::class, 'update']);
Route::get('classrooms/{classroom}/enrollments', [ClassroomApiController::class, 'getEnrollments']);

// Classroom linking endpoints
Route::prefix('classrooms/{classroom}')->group(function () {
    Route::get('linked-enrollments', [ClassroomLinkingController::class, 'linked']);
    Route::get('history', [ClassroomLinkingController::class, 'history']); // Histórico completo de alunos
    Route::get('eligible-enrollments', [ClassroomLinkingController::class, 'eligible']);
    Route::post('link-enrollment', [ClassroomLinkingController::class, 'link']);
    Route::post('transfer-enrollment', [ClassroomLinkingController::class, 'transfer']);
    Route::post('unlink-enrollment', [ClassroomLinkingController::class, 'unlink']);
});

// Rota para obter informações detalhadas das turmas
Route::get('classrooms-detailed', function () {
    $classrooms = \App\Models\Classroom::withCount(['enrollments as total_enrollments'])
        ->active()
        ->get()
        ->map(function ($classroom) {
            return [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'year' => $classroom->year,
                'shift' => $classroom->shift,
                'max_students' => $classroom->max_students,
                'current_students' => $classroom->getEnrolledStudentsCount(),
                'available_slots' => $classroom->getAvailableSlots(),
                'is_active' => $classroom->is_active,
                'occupation_percentage' => $classroom->max_students > 0 
                    ? round(($classroom->getEnrolledStudentsCount() / $classroom->max_students) * 100, 2)
                    : 0
            ];
        });
    
    return response()->json($classrooms);
});

// Rotas API para Guardian (usadas pelo frontend)
Route::get('guardians', [GuardianApiController::class, 'index']);
Route::get('guardians-autocomplete', [GuardianController::class, 'autocomplete']);

// Rotas API para Student (usadas pelo frontend)  
Route::get('students', [StudentApiController::class, 'index']);
Route::get('students/{student}', [StudentApiController::class, 'show']);
Route::get('students-autocomplete', [StudentController::class, 'autocomplete']);

// Rota API para buscar guardians não vinculados a um aluno
Route::get('students/{student}/guardians/search-not-linked', [GuardianController::class, 'searchNotLinked']);

// Rota API para buscar responsáveis vinculados a um aluno
Route::get('students/{student}/guardians', function ($student) {
    $student = \App\Models\Student::findOrFail($student);
    $guardians = $student->guardians()->get();
    return response()->json($guardians);
});

// Rotas para finanças de matrícula
Route::get('enrollments/{enrollment}/financial-summary', function ($enrollment) {
    $enrollment = \App\Models\Enrollment::findOrFail($enrollment);
    $financeService = new \App\Services\EnrollmentFinanceService();
    return $financeService->getFinancialSummary($enrollment);
});

// Rota para adicionar serviços à matrícula
Route::post('enrollments/{enrollment}/add-services', function ($enrollment, \Illuminate\Http\Request $request) {
    $enrollment = \App\Models\Enrollment::findOrFail($enrollment);
    $financeService = new \App\Services\EnrollmentFinanceService();
    
    $serviceIds = $request->input('service_ids', []);
    
    if (empty($serviceIds)) {
        return response()->json(['error' => 'Nenhum serviço selecionado'], 400);
    }
    
    try {
        $invoices = $financeService->createServiceInvoices($enrollment, $serviceIds);
        return response()->json([
            'message' => 'Serviços adicionados com sucesso!',
            'invoices' => $invoices
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Rota para buscar serviços da matrícula
Route::get('enrollments/{enrollment}/services', function ($enrollment) {
    $enrollment = \App\Models\Enrollment::findOrFail($enrollment);
    
    $services = $enrollment->invoices()
        ->where('type', 'service')
        ->get()
        ->map(function ($invoice) {
            return [
                'id' => $invoice->id, // ID da fatura
                'invoice_number' => $invoice->invoice_number,
                'name' => $invoice->description, // Nome vem da descrição da fatura
                'description' => $invoice->notes ?? 'Serviço adicional',
                'amount' => $invoice->amount, // Valor original da fatura
                'formatted_amount' => $invoice->formatted_amount,
                'status' => $invoice->status,
                'status_label' => $invoice->status_label,
                'due_date' => $invoice->due_date,
                'paid_date' => $invoice->paid_date,
                'is_classroom_linked' => false, // Serviços adicionais são globais
                'category' => 'servico-adicional'
            ];
        });
    
    return response()->json($services);
});

// Rota para remover serviço da matrícula
Route::delete('enrollments/{enrollment}/services/{invoice}', function ($enrollment, $invoice) {
    $enrollment = \App\Models\Enrollment::findOrFail($enrollment);
    $invoice = \App\Models\EnrollmentInvoice::findOrFail($invoice);
    
    // Verificar se a fatura pertence à matrícula
    if ($invoice->enrollment_id !== $enrollment->id) {
        return response()->json(['error' => 'Fatura não pertence a esta matrícula'], 403);
    }
    
    // Verificar se a fatura é do tipo serviço
    if ($invoice->type !== 'service') {
        return response()->json(['error' => 'Apenas faturas de serviços podem ser removidas'], 400);
    }
    
    // Verificar se a fatura não foi paga
    if ($invoice->status === 'paid') {
        return response()->json(['error' => 'Não é possível remover serviço já pago'], 400);
    }
    
    try {
        // Remover a fatura
        $invoice->delete();
        
        return response()->json([
            'message' => 'Serviço removido com sucesso!',
            'removed_invoice' => $invoice
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao remover serviço: ' . $e->getMessage()], 500);
    }
});

// Rota para estornar pagamento
Route::post('enrollments/{enrollment}/payments/{payment}/refund', function ($enrollment, $payment, \Illuminate\Http\Request $request) {
    $enrollment = \App\Models\Enrollment::findOrFail($enrollment);
    $payment = \App\Models\EnrollmentPayment::findOrFail($payment);
    
    // Verificar se o pagamento pertence à matrícula
    if ($payment->enrollment_id !== $enrollment->id) {
        return response()->json(['error' => 'Pagamento não pertence a esta matrícula'], 403);
    }
    
    // Verificar se o pagamento pode ser estornado
    if (!$payment->canBeRefunded()) {
        return response()->json(['error' => 'Este pagamento não pode ser estornado'], 400);
    }
    
    $validatedData = $request->validate([
        'reason' => 'nullable|string|max:500'
    ]);
    
    try {
        // Estornar o pagamento
        $payment->refund($validatedData['reason'] ?? null);
        
        return response()->json([
            'message' => 'Pagamento estornado com sucesso!',
            'payment' => $payment->fresh(),
            'financial_summary' => $enrollment->getFinancialSummary()
        ]);
        
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao estornar pagamento: ' . $e->getMessage()], 500);
    }
});

// Rota para atualizar pagamento
Route::put('enrollments/{enrollment}/payments/{payment}/update', function ($enrollment, $payment, \Illuminate\Http\Request $request) {
    $enrollment = \App\Models\Enrollment::findOrFail($enrollment);
    $payment = \App\Models\EnrollmentPayment::findOrFail($payment);
    
    // Verificar se o pagamento pertence à matrícula
    if ($payment->enrollment_id !== $enrollment->id) {
        return response()->json(['error' => 'Pagamento não pertence a esta matrícula'], 403);
    }
    
    // Verificar se o pagamento pode ser editado
    if (!$payment->canBeEdited()) {
        return response()->json(['error' => 'Este pagamento não pode ser editado'], 400);
    }
    
    $validatedData = $request->validate([
        'amount' => 'required|numeric|min:0',
        'original_amount' => 'nullable|numeric|min:0',
        'interest_amount' => 'nullable|numeric|min:0',
        'discount_amount' => 'nullable|numeric|min:0',
        'method' => 'required|in:cash,pix,credit_card,debit_card,bank_transfer,check,other',
        'payment_date' => 'required|date',
        'reference' => 'nullable|string|max:255',
        'notes' => 'nullable|string|max:1000',
        'paid_by_guardian_id' => 'required|integer|exists:guardians,id'
    ]);
    
    try {
        // Atualizar o pagamento
        $payment->update([
            'amount' => $validatedData['amount'],
            'original_amount' => $validatedData['original_amount'] ?? $validatedData['amount'],
            'interest_amount' => $validatedData['interest_amount'] ?? 0,
            'discount_amount' => $validatedData['discount_amount'] ?? 0,
            'method' => $validatedData['method'],
            'payment_date' => $validatedData['payment_date'],
            'reference' => $validatedData['reference'],
            'notes' => $validatedData['notes'],
            'paid_by_guardian_id' => $validatedData['paid_by_guardian_id']
        ]);
        
        return response()->json([
            'message' => 'Pagamento atualizado com sucesso!',
            'payment' => $payment->fresh(),
            'financial_summary' => $enrollment->getFinancialSummary()
        ]);
        
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao atualizar pagamento: ' . $e->getMessage()], 500);
    }
});

// Rota para reativar serviço estornado
Route::post('enrollments/{enrollment}/services/{invoice}/reactivate', function ($enrollment, $invoice) {
    $enrollment = \App\Models\Enrollment::findOrFail($enrollment);
    $invoice = \App\Models\EnrollmentInvoice::findOrFail($invoice);
    
    if ($invoice->enrollment_id !== $enrollment->id) {
        return response()->json(['error' => 'Serviço não pertence a esta matrícula'], 403);
    }
    
    try {
        $invoice->reactivate();
        
        return response()->json([
            'message' => 'Serviço reativado com sucesso!',
            'invoice' => $invoice->fresh(),
            'financial_summary' => $enrollment->getFinancialSummary()
        ]);
        
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao reativar serviço: ' . $e->getMessage()], 500);
    }
});

// Rota para deletar serviço estornado
Route::delete('enrollments/{enrollment}/services/{invoice}/permanent-delete', function ($enrollment, $invoice) {
    $enrollment = \App\Models\Enrollment::findOrFail($enrollment);
    $invoice = \App\Models\EnrollmentInvoice::findOrFail($invoice);
    
    if ($invoice->enrollment_id !== $enrollment->id) {
        return response()->json(['error' => 'Serviço não pertence a esta matrícula'], 403);
    }
    
    // Só permite deletar serviços estornados
    if ($invoice->status !== 'refunded') {
        return response()->json(['error' => 'Apenas serviços estornados podem ser deletados'], 400);
    }
    
    try {
        // Deletar pagamentos relacionados primeiro
        $invoice->payments()->delete();
        
        // Deletar a fatura
        $invoice->delete();
        
        return response()->json([
            'message' => 'Serviço deletado com sucesso!',
            'financial_summary' => $enrollment->getFinancialSummary()
        ]);
        
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao deletar serviço: ' . $e->getMessage()], 500);
    }
});

// Rota para registrar pagamento de faturas
Route::post('enrollments/{enrollment}/register-payment', function ($enrollment, \Illuminate\Http\Request $request) {
    $enrollment = \App\Models\Enrollment::findOrFail($enrollment);
    $financeService = new \App\Services\EnrollmentFinanceService();
    
    $validatedData = $request->validate([
        'invoice_ids' => 'required|array',
        'invoice_ids.*' => 'integer|exists:enrollment_invoices,id',
        'amount' => 'required|numeric|min:0',
        'interest_amount' => 'nullable|numeric|min:0',
        'discount_amount' => 'nullable|numeric|min:0',
        'method' => 'required|in:cash,pix,credit_card,debit_card,bank_transfer,check,other',
        'payment_date' => 'required|date',
        'reference' => 'nullable|string|max:255',
        'notes' => 'nullable|string|max:1000',
        'paid_by_guardian_id' => 'required|integer|exists:guardians,id'
    ]);
    
    try {
        // Preparar descrição com informações de desconto e juros
        $description = 'Pagamento de Serviços - ' . count($validatedData['invoice_ids']) . ' serviço(s)';
        $notes = $validatedData['notes'] ?? '';
        
        if ($validatedData['interest_amount'] > 0) {
            $notes .= ($notes ? ' | ' : '') . 'Juros: R$ ' . number_format($validatedData['interest_amount'], 2, ',', '.');
        }
        
        if ($validatedData['discount_amount'] > 0) {
            $notes .= ($notes ? ' | ' : '') . 'Desconto: R$ ' . number_format($validatedData['discount_amount'], 2, ',', '.');
        }
        
        // Calcular valor original dos serviços selecionados
        $totalOriginalAmount = 0;
        foreach ($validatedData['invoice_ids'] as $invoiceId) {
            $invoice = \App\Models\EnrollmentInvoice::find($invoiceId);
            if ($invoice && $invoice->enrollment_id == $enrollment->id) {
                $totalOriginalAmount += $invoice->amount;
            }
        }
        
        // Registrar o pagamento
        $payment = $financeService->registerPayment($enrollment, [
            'invoice_id' => $validatedData['invoice_ids'][0], // Pagamento principal para o primeiro serviço
            'type' => 'service',
            'description' => $description,
            'amount' => $validatedData['amount'], // Valor final pago
            'original_amount' => $totalOriginalAmount, // Valor original dos serviços
            'discount_amount' => $validatedData['discount_amount'] ?? 0,
            'interest_amount' => $validatedData['interest_amount'] ?? 0,
            'method' => $validatedData['method'],
            'payment_date' => $validatedData['payment_date'],
            'reference' => $validatedData['reference'],
            'status' => 'confirmed',
            'notes' => $notes,
            'paid_by_guardian_id' => $validatedData['paid_by_guardian_id']
        ]);
        
        // Marcar todos os serviços como pagos
        foreach ($validatedData['invoice_ids'] as $invoiceId) {
            $invoice = \App\Models\EnrollmentInvoice::find($invoiceId);
            if ($invoice && $invoice->enrollment_id == $enrollment->id) {
                $invoice->markAsPaid($validatedData['payment_date']);
            }
        }
        
        return response()->json([
            'message' => 'Pagamento registrado com sucesso!',
            'payment' => $payment,
            'payment_number' => $payment->payment_number
        ]);
        
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Rotas para gestão de irmãos
Route::middleware(['auth:sanctum'])->group(function () {
    // Grupos de irmãos
    Route::apiResource('sibling-groups', \App\Http\Controllers\Api\SiblingGroupController::class);
    
    // Irmãos de responsáveis
    Route::get('guardians/{guardian}/siblings', [\App\Http\Controllers\Api\GuardianSiblingController::class, 'index']);
    Route::post('guardians/{guardian}/siblings', [\App\Http\Controllers\Api\GuardianSiblingController::class, 'store']);
    Route::delete('guardians/{guardian}/siblings/{sibling}', [\App\Http\Controllers\Api\GuardianSiblingController::class, 'destroy']);
    Route::get('guardians/{guardian}/has-active-siblings', [\App\Http\Controllers\Api\GuardianSiblingController::class, 'hasActiveSiblings']);
});

// ========================================
// ROTAS DO SISTEMA DE MENSALIDADES
// ========================================

// Rotas de mensalidades SEM middleware por enquanto (para testes)
// TODO: Adicionar middleware de autenticação depois

// Grupo de rotas para mensalidades (Contratos)
Route::prefix('monthly-fees')->name('monthly-fees.')->group(function () {
    Route::get('/', [MonthlyFeeController::class, 'index'])->name('index');
    Route::post('/', [MonthlyFeeController::class, 'store'])->name('store');
    Route::get('{id}', [MonthlyFeeController::class, 'show'])->name('show');
    Route::put('{id}', [MonthlyFeeController::class, 'update'])->name('update');
    Route::delete('{id}', [MonthlyFeeController::class, 'destroy'])->name('destroy');
    Route::get('{id}/installments', [MonthlyFeeController::class, 'installments'])->name('installments');
    Route::post('{id}/suspend', [MonthlyFeeController::class, 'suspend'])->name('suspend');
    Route::post('{id}/reactivate', [MonthlyFeeController::class, 'reactivate'])->name('reactivate');
});

// Grupo de rotas para parcelas de mensalidades
Route::prefix('installments')->name('installments.')->group(function () {
    Route::get('/', [MonthlyFeeInstallmentController::class, 'index'])->name('index');
    Route::get('overdue', [MonthlyFeeInstallmentController::class, 'overdue'])->name('overdue');
    Route::get('pending', [MonthlyFeeInstallmentController::class, 'pending'])->name('pending');
    Route::get('month/{month}', [MonthlyFeeInstallmentController::class, 'byMonth'])->name('by-month');
    Route::get('{id}', [MonthlyFeeInstallmentController::class, 'show'])->name('show');
    Route::put('{id}', [MonthlyFeeInstallmentController::class, 'update'])->name('update');
    Route::post('{id}/pay', [MonthlyFeeInstallmentController::class, 'pay'])->name('pay');
    Route::post('{id}/cancel', [MonthlyFeeInstallmentController::class, 'cancel'])->name('cancel');
    Route::post('{id}/waive', [MonthlyFeeInstallmentController::class, 'waive'])->name('waive');
});

// Grupo de rotas para pagamentos de mensalidades
Route::prefix('monthly-payments')->name('monthly-payments.')->group(function () {
    Route::get('/', [MonthlyFeePaymentController::class, 'index'])->name('index');
    Route::post('/', [MonthlyFeePaymentController::class, 'store'])->name('store');
    Route::get('statistics', [MonthlyFeePaymentController::class, 'statistics'])->name('statistics');
    Route::get('{id}', [MonthlyFeePaymentController::class, 'show'])->name('show');
    Route::post('{id}/confirm', [MonthlyFeePaymentController::class, 'confirm'])->name('confirm');
    Route::post('{id}/refund', [MonthlyFeePaymentController::class, 'refund'])->name('refund');
    Route::post('{id}/cancel', [MonthlyFeePaymentController::class, 'cancel'])->name('cancel');
    Route::get('{id}/receipt', [MonthlyFeePaymentController::class, 'receipt'])->name('receipt');
});
