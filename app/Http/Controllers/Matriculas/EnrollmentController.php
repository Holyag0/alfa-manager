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
use App\Services\ServiceStudent;
use App\Services\ServiceGuardian;

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
        // Retrieve data from session instead of request
        $studentData = session('enrollment_wizard.student');
        $guardianData = session('enrollment_wizard.guardian');
        $enrollmentData = session('enrollment_wizard.enrollment');
        
        // Validate that all required data is present in session
        if (!$studentData || !$guardianData || !$enrollmentData) {
            return redirect()->back()->withErrors([
                'wizard' => 'Por favor, complete todos os passos do wizard.'
            ]);
        }
        
        try {
            // Create student using ServiceStudent
            $studentService = app(ServiceStudent::class);
            $student = $studentService->create($studentData);
            
            // Create guardian using ServiceGuardian
            $guardianService = app(ServiceGuardian::class);
            $guardian = $guardianService->create($guardianData);
            
            // Link guardian to student
            $guardianService->attachToStudent($guardian->id, $student->id);
            
            // Create enrollment using EnrollmentService
            $enrollment = $this->service->createEnrollment([
                'student_id' => $student->id,
                'guardian_id' => $guardian->id,
                'classroom_id' => $enrollmentData['classroom_id'],
                'enrollment_date' => $enrollmentData['enrollment_date'],
                'status' => $enrollmentData['status'] ?? 'active',
                'process' => $enrollmentData['process'] ?? 'completa',
                'notes' => $enrollmentData['notes'] ?? null,
            ]);
            
            // Clear wizard data from session
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