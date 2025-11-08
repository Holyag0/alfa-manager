<?php

/**
 * EXEMPLOS DE USO DO SISTEMA DE HISTÓRICO DE MATRÍCULAS
 * 
 * Este arquivo contém exemplos práticos de como usar o novo sistema
 * de preservação de histórico de matrículas.
 */

use App\Models\Enrollment;
use App\Models\Student;
use App\Services\EnrollmentService;

// ============================================
// 1. CRIAR NOVA MATRÍCULA (com ano letivo)
// ============================================

$enrollment = Enrollment::create([
    'student_id' => 1,
    'guardian_id' => 1,
    'classroom_id' => 5,
    'academic_year' => 2024,  // ← NOVO: obrigatório
    'status' => 'active',
    'process' => 'completa',
    'enrollment_date' => now(),
    'notes' => 'Primeira matrícula do aluno',
]);

// ============================================
// 2. RENOVAR MATRÍCULA PARA NOVO ANO
// ============================================

$enrollmentService = app(EnrollmentService::class);

// Renovação simples (mantém responsável e sem turma definida)
$novaMatricula = $enrollmentService->renewEnrollment($enrollment->id, [
    'academic_year' => 2025,
]);

// Renovação completa (muda responsável e define nova turma)
$novaMatricula = $enrollmentService->renewEnrollment($enrollment->id, [
    'academic_year' => 2025,
    'classroom_id' => 10,  // Nova turma (2º ano)
    'guardian_id' => 2,    // Novo responsável
]);

// ============================================
// 3. CONSULTAR HISTÓRICO COMPLETO DO ALUNO
// ============================================

// Opção 1: Usando método estático
$historico = Enrollment::getStudentHistory($studentId);

// Opção 2: Query manual
$historico = Enrollment::where('student_id', $studentId)
    ->with(['classroom', 'guardian'])
    ->orderBy('academic_year', 'desc')
    ->get();

// Exibir histórico
foreach ($historico as $enrollment) {
    echo "Ano: {$enrollment->academic_year}\n";
    echo "Turma: {$enrollment->classroom->name}\n";
    echo "Status: {$enrollment->status}\n";
    echo "Responsável: {$enrollment->guardian->name}\n";
    echo "---\n";
}

// ============================================
// 4. BUSCAR MATRÍCULA ATIVA ATUAL DO ALUNO
// ============================================

$matriculaAtual = Enrollment::where('student_id', $studentId)
    ->where('status', 'active')
    ->first();

if ($matriculaAtual) {
    echo "Aluno está matriculado em {$matriculaAtual->academic_year}";
    echo " na turma {$matriculaAtual->classroom->name}";
}

// ============================================
// 5. VERIFICAR SE ALUNO JÁ TEM MATRÍCULA EM UM ANO
// ============================================

$temMatricula2024 = Enrollment::hasActiveEnrollmentInYear($studentId, 2024);

if ($temMatricula2024) {
    echo "Aluno já possui matrícula ativa para 2024";
} else {
    echo "Aluno não possui matrícula para 2024";
}

// ============================================
// 6. LISTAR TODAS MATRÍCULAS DE UM ANO
// ============================================

// Todas as matrículas de 2024
$matriculas2024 = Enrollment::byAcademicYear(2024)->get();

// Apenas matrículas ativas de 2024
$matriculasAtivas2024 = Enrollment::byAcademicYear(2024)
    ->where('status', 'active')
    ->with(['student', 'classroom'])
    ->get();

// ============================================
// 7. BUSCAR ALUNOS DE UMA TURMA EM UM ANO
// ============================================

$classroomId = 5;
$year = 2024;

$alunos = Student::whereHas('enrollments', function($q) use ($classroomId, $year) {
    $q->where('classroom_id', $classroomId)
      ->where('academic_year', $year)
      ->where('status', 'active');
})->get();

// ============================================
// 8. CONSULTAS FINANCEIRAS POR ANO
// ============================================

// Buscar matrícula de um aluno em 2024
$enrollment2024 = Enrollment::where('student_id', $studentId)
    ->where('academic_year', 2024)
    ->first();

if ($enrollment2024) {
    // Faturas de 2024
    $faturas2024 = $enrollment2024->invoices;
    
    // Pagamentos de 2024
    $pagamentos2024 = $enrollment2024->payments;
    
    // Total devido em 2024
    $totalDevido2024 = $enrollment2024->getTotalDue();
    
    // Total pago em 2024
    $totalPago2024 = $enrollment2024->getTotalPaid();
    
    echo "Ano 2024:\n";
    echo "Total Devido: R$ " . number_format($totalDevido2024, 2, ',', '.') . "\n";
    echo "Total Pago: R$ " . number_format($totalPago2024, 2, ',', '.') . "\n";
}

// ============================================
// 9. RELATÓRIO: RECEITA POR ANO
// ============================================

use Illuminate\Support\Facades\DB;

$receitaPorAno = DB::table('enrollments')
    ->join('enrollment_invoices', 'enrollments.id', '=', 'enrollment_invoices.enrollment_id')
    ->where('enrollment_invoices.status', 'paid')
    ->select('enrollments.academic_year', DB::raw('SUM(enrollment_invoices.amount) as receita_total'))
    ->groupBy('enrollments.academic_year')
    ->orderBy('enrollments.academic_year', 'desc')
    ->get();

foreach ($receitaPorAno as $item) {
    echo "Ano {$item->academic_year}: R$ " . number_format($item->receita_total, 2, ',', '.') . "\n";
}

// ============================================
// 10. RELATÓRIO: ALUNOS ATIVOS POR ANO
// ============================================

$alunosAtivosPorAno = DB::table('enrollments')
    ->where('status', 'active')
    ->select('academic_year', DB::raw('COUNT(*) as total_alunos'))
    ->groupBy('academic_year')
    ->orderBy('academic_year', 'desc')
    ->get();

foreach ($alunosAtivosPorAno as $item) {
    echo "Ano {$item->academic_year}: {$item->total_alunos} alunos ativos\n";
}

// ============================================
// 11. USAR SCOPES PERSONALIZADOS
// ============================================

// Matrícula ativa de um aluno em um ano específico
$enrollment = Enrollment::activeForStudentInYear($studentId, 2024)->first();

// Todas as matrículas ativas
$matriculasAtivas = Enrollment::active()->get();

// Todas as matrículas finalizadas
$matriculasFinalizadas = Enrollment::completed()->get();

// Matrículas de 2024
$matriculas2024 = Enrollment::byAcademicYear(2024)->get();

// ============================================
// 12. VERIFICAR SE MATRÍCULA PODE SER RENOVADA
// ============================================

$enrollment = Enrollment::find(1);

if ($enrollment->canBeRenewed()) {
    echo "Matrícula pode ser renovada";
    // Processar renovação...
} else {
    echo "Matrícula não pode ser renovada (status: {$enrollment->status})";
}

// ============================================
// 13. BUSCAR PROGRESSÃO DO ALUNO AO LONGO DOS ANOS
// ============================================

$progressao = Enrollment::where('student_id', $studentId)
    ->with(['classroom'])
    ->orderBy('academic_year', 'asc')
    ->get()
    ->map(function($enrollment) {
        return [
            'ano' => $enrollment->academic_year,
            'turma' => $enrollment->classroom->name ?? 'Sem turma',
            'status' => $enrollment->status,
            'processo' => $enrollment->process,
        ];
    });

echo "Progressão do aluno:\n";
foreach ($progressao as $item) {
    echo "{$item['ano']}: {$item['turma']} ({$item['status']})\n";
}

// ============================================
// 14. LISTAR ANOS LETIVOS DISPONÍVEIS
// ============================================

$anosDisponiveis = Enrollment::select('academic_year')
    ->distinct()
    ->orderBy('academic_year', 'desc')
    ->pluck('academic_year');

echo "Anos letivos com matrículas:\n";
foreach ($anosDisponiveis as $ano) {
    echo "- {$ano}\n";
}

// ============================================
// 15. COMPARAR DOIS ANOS LETIVOS
// ============================================

function compararAnos($studentId, $ano1, $ano2) {
    $enrollment1 = Enrollment::where('student_id', $studentId)
        ->where('academic_year', $ano1)
        ->with(['classroom', 'guardian'])
        ->first();
        
    $enrollment2 = Enrollment::where('student_id', $studentId)
        ->where('academic_year', $ano2)
        ->with(['classroom', 'guardian'])
        ->first();
    
    return [
        $ano1 => [
            'turma' => $enrollment1->classroom->name ?? 'N/A',
            'responsavel' => $enrollment1->guardian->name ?? 'N/A',
            'total_pago' => $enrollment1->getTotalPaid(),
        ],
        $ano2 => [
            'turma' => $enrollment2->classroom->name ?? 'N/A',
            'responsavel' => $enrollment2->guardian->name ?? 'N/A',
            'total_pago' => $enrollment2->getTotalPaid(),
        ],
    ];
}

$comparacao = compararAnos($studentId, 2023, 2024);
print_r($comparacao);


