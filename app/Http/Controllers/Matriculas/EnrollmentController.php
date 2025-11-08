<?php

namespace App\Http\Controllers\Matriculas;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnrollmentRequest;
use App\Http\Requests\WizardRequest;
use App\Http\Requests\EnrollmentWizardRequest;
use App\Services\EnrollmentService;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Services\ServiceStudent;
use App\Services\ServiceGuardian;
use App\Services\EnrollmentFinanceService;

class EnrollmentController extends Controller
{
    protected $service;

    public function __construct(EnrollmentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['student', 'student_id', 'guardian_id', 'classroom_id', 'status']);
        $perPage = $request->input('per_page', 10);
        $paginated = $this->service->searchEnrollments($filters, $perPage);
        $classrooms = Classroom::all();
        return Inertia::render('Matriculas/Index', [
            'enrollments' => $paginated,
            'classrooms' => $classrooms,
        ]);
    }

    public function create()
    {
        // Limpar dados antigos do wizard ao iniciar nova matrícula
        session()->forget('enrollment_wizard');
        
        $classrooms = Classroom::all();
        $wizardData = [
            'student' => null,
            'guardian' => null,
            'enrollment' => null,
        ];
        
        return Inertia::render('Matriculas/Wizard', [
            'classrooms' => $classrooms,
            'wizardData' => $wizardData,
        ]);
    }

    /**
     * Show simple wizard for enrollment
     */
    public function createSimple()
    {
        // Limpar dados antigos do wizard ao iniciar nova matrícula
        session()->forget('enrollment_wizard');
        
        return Inertia::render('Matriculas/SimpleWizard');
    }

    public function edit($id)
    {
        $enrollment = Enrollment::with(['student', 'guardian', 'classroom'])->findOrFail($id);
        $classrooms = Classroom::all();
        
        return Inertia::render('Matriculas/Edit', [
            'enrollment' => $enrollment,
            'classrooms' => $classrooms,
        ]);
    }

    public function store(EnrollmentRequest $request)
    {
        try {
            $data = $request->validated();
            $enrollment = $this->service->createEnrollment($data);
            return redirect()->route('matriculas.index')->with('success', 'Matrícula criada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'enrollment' => $e->getMessage()
            ]);
        }
    }

    public function cancel(Request $request, $id)
    {
        $reason = $request->input('reason');
        $enrollment = $this->service->cancelEnrollment($id, $reason);
        return redirect()->route('matriculas.index')->with('success', 'Matrícula cancelada com sucesso!');
    }

    public function changeClassroom(Request $request, $id)
    {
        $newClassroomId = $request->input('classroom_id');
        $enrollment = $this->service->changeClassroom($id, $newClassroomId);
        return redirect()->route('matriculas.index')->with('success', 'Turma alterada com sucesso!');
    }

    public function update(EnrollmentRequest $request, $id)
    {
        $data = $request->validated();
        $enrollment = $this->service->updateEnrollment($id, $data);
        return redirect()->route('matriculas.edit', $id)->with('success', 'Matrícula atualizada com sucesso!');
    }



    public function destroy($id)
    {
        $enrollment = Enrollment::findOrFail($id);
        $enrollment->delete();
        return redirect()->route('matriculas.index')->with('success', 'Matrícula excluída com sucesso!');
    }

    /**
     * Store wizard step data in session
     */
    public function wizardStore(WizardRequest $request)
    {
        $step = $request->input('step');
        $validatedData = $request->validated();
        
        switch ($step) {
            case 'aluno':
                session(['enrollment_wizard.student' => $validatedData['student']]);
                session(['enrollment_wizard.last_activity' => now()]);
                break;
                
            case 'responsavel':
                session(['enrollment_wizard.guardian' => $validatedData['guardian']]);
                session(['enrollment_wizard.last_activity' => now()]);
                break;
                
            case 'matricula':
                // Verificar se há vagas na turma usando método consistente do model
                $classroom = Classroom::findOrFail($validatedData['enrollment']['classroom_id']);
                
                if (!$classroom->hasAvailableSlots()) {
                    $availableSlots = $classroom->getAvailableSlots();
                    $enrolledCount = $classroom->getEnrolledStudentsCount();
                    
                    return redirect()->back()->withErrors([
                        'enrollment.classroom_id' => "Esta turma não possui vagas disponíveis. Vagas ocupadas: {$enrolledCount}/{$classroom->max_students}"
                    ]);
                }
                
                session(['enrollment_wizard.enrollment' => $validatedData['enrollment']]);
                session(['enrollment_wizard.last_activity' => now()]);
                break;
        }
        
        return redirect()->back();
    }

    /**
     * Complete the wizard and create all records
     */
    public function wizardComplete(EnrollmentWizardRequest $request)
    {
        // Get validated data from request
        $validatedData = $request->validated();
        
        // Log para debug
        \Log::info('WizardComplete chamado com dados:', $validatedData);
        
        try {
            // Get existing student and guardian from IDs (dados já foram cadastrados nos steps anteriores)
            $student = \App\Models\Student::find($validatedData['student_id']);
            $guardian = \App\Models\Guardian::find($validatedData['guardian_id']);
            
            if (!$student || !$guardian) {
                return redirect()->back()->withErrors([
                    'wizard' => 'Aluno ou responsável não encontrado.'
                ]);
            }
            
            // Link guardian to student if not already linked
            $guardianService = app(ServiceGuardian::class);
            $guardianService->attachToStudent($guardian->id, $student->id);
            
            // Create enrollment using EnrollmentService
            $enrollment = $this->service->createEnrollment([
                'student_id' => $student->id,
                'guardian_id' => $guardian->id,
                'classroom_id' => $validatedData['classroom_id'] ?? null,
                'academic_year' => $validatedData['academic_year'] ?? now()->year,
                'enrollment_date' => $validatedData['enrollment_date'],
                'status' => $validatedData['status'] ?? 'active',
                'process' => $validatedData['process'] ?? 'completa',
                'notes' => $validatedData['notes'] ?? null,
            ]);
            
            // Criar fatura automática baseada no processo da matrícula
            try {
                $enrollmentFinanceService = app(EnrollmentFinanceService::class);
                $invoice = $enrollmentFinanceService->createAutomaticInvoice($enrollment);
                
                $successMessage = 'Matrícula criada com sucesso!';
                if ($invoice) {
                    $serviceName = $invoice->description;
                    $amount = 'R$ ' . number_format($invoice->amount, 2, ',', '.');
                    $successMessage .= " Fatura automática criada: {$serviceName} - {$amount}";
                }
            } catch (\Exception $e) {
                // Se não conseguir criar fatura automática, apenas logar o erro
                Log::warning('Erro ao criar fatura automática: ' . $e->getMessage());
                $successMessage = 'Matrícula criada com sucesso! (Fatura será criada manualmente)';
            }
            
            // Clear wizard data from session
            session()->forget('enrollment_wizard');
            
            return redirect()->route('matriculas.index')->with('success', $successMessage);
            
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'wizard' => 'Erro ao processar matrícula: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Reset wizard data
     */
    public function wizardReset()
    {
        session()->forget('enrollment_wizard');
        return redirect()->route('matriculas.create');
    }

    /**
     * Renovar matrícula para novo ano letivo
     */
    public function renew(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'academic_year' => 'required|integer|min:2000|max:' . (now()->year + 5),
                'classroom_id' => 'nullable|exists:classrooms,id',
                'guardian_id' => 'nullable|exists:guardians,id',
            ]);

            $newEnrollment = $this->service->renewEnrollment($id, $data);
            
            return redirect()
                ->route('matriculas.edit', $newEnrollment->id)
                ->with('success', "Matrícula renovada com sucesso para o ano {$data['academic_year']}!");
            
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'renewal' => 'Erro ao renovar matrícula: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Visualizar histórico de matrículas do aluno
     */
    public function history($studentId)
    {
        $student = Student::with(['enrollments' => function($query) {
            $query->with(['classroom', 'guardian'])
                  ->orderBy('academic_year', 'desc');
        }])->findOrFail($studentId);

        return Inertia::render('Matriculas/History', [
            'student' => $student,
            'enrollments' => $student->enrollments,
        ]);
    }

    /**
     * Listar matrículas por ano letivo
     */
    public function byYear(Request $request, $year)
    {
        $enrollments = Enrollment::with(['student', 'guardian', 'classroom'])
            ->byAcademicYear($year)
            ->orderBy('id', 'desc')
            ->paginate(20);

        $classrooms = Classroom::all();

        return Inertia::render('Matriculas/ByYear', [
            'year' => $year,
            'enrollments' => $enrollments,
            'classrooms' => $classrooms,
        ]);
    }
} 