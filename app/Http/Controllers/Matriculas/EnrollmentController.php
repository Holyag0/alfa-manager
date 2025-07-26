<?php

namespace App\Http\Controllers\Matriculas;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnrollmentRequest;
use App\Http\Requests\WizardRequest;
use App\Services\EnrollmentService;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

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
        $classrooms = Classroom::all();
        $wizardData = [
            'student' => session('enrollment_wizard.student'),
            'guardian' => session('enrollment_wizard.guardian'),
            'enrollment' => session('enrollment_wizard.enrollment'),
        ];
        
        return Inertia::render('Matriculas/Wizard', [
            'classrooms' => $classrooms,
            'wizardData' => $wizardData,
        ]);
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
        
        $data = $request->validated();
        $enrollment = $this->service->createEnrollment($data);
        return redirect()->route('matriculas.index')->with('success', 'Matrícula criada com sucesso!');
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
                break;
                
            case 'responsavel':
                session(['enrollment_wizard.guardian' => $validatedData['guardian']]);
                break;
                
            case 'matricula':
                // Verificar se há vagas na turma
                $classroom = Classroom::findOrFail($validatedData['enrollment']['classroom_id']);
                $activeEnrollments = Enrollment::where('classroom_id', $classroom->id)
                    ->where('status', 'active')
                    ->count();
                    
                if ($activeEnrollments >= $classroom->vacancies) {
                    return redirect()->back()->withErrors([
                        'enrollment.classroom_id' => 'Esta turma não possui vagas disponíveis.'
                    ]);
                }
                
                session(['enrollment_wizard.enrollment' => $validatedData['enrollment']]);
                break;
        }
        
        return redirect()->back();
    }

    /**
     * Complete the wizard and create all records
     */
    public function wizardComplete(Request $request)
    {
        $data = $request->all();
        
        // Validar dados obrigatórios
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'guardian_id' => 'required|exists:guardians,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'status' => 'required|string',
            'enrollment_date' => 'required|date',
        ]);
        
        try {
            // Buscar student e guardian já criados
            $student = Student::findOrFail($data['student_id']);
            $guardian = Guardian::findOrFail($data['guardian_id']);
            
            // Vincular responsável ao aluno (se ainda não estiver vinculado)
            if (!$student->guardians()->where('guardian_id', $guardian->id)->exists()) {
                $student->guardians()->attach($guardian->id);
            }
            
            // Criar a matrícula
            $enrollment = Enrollment::create([
                'student_id' => $student->id,
                'guardian_id' => $guardian->id,
                'classroom_id' => $data['classroom_id'],
                'enrollment_date' => $data['enrollment_date'],
                'status' => $data['status'],
                'process' => $data['process'] ?? 'completa',
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Limpar dados do wizard da sessão
            session()->forget('enrollment_wizard');
            
            return redirect()->route('matriculas.index')->with('success', 'Matrícula criada com sucesso!');
            
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
} 