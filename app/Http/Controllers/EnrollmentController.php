<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnrollmentRequest;
use App\Services\EnrollmentService;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Classroom;
use Illuminate\Http\Request;
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
        return Inertia::render('Matriculas/Wizard');
    }

    public function edit($id)
    {
        $enrollment = Enrollment::with(['student', 'guardian', 'classroom'])->findOrFail($id);
        $students = Student::all();
        $guardians = Guardian::all();
        $classrooms = Classroom::all();
        return Inertia::render('Matriculas/Edit', [
            'enrollment' => $enrollment,
            'students' => $students,
            'guardians' => $guardians,
            'classrooms' => $classrooms,
        ]);
    }

    public function store(EnrollmentRequest $request)
    {
        $data = $request->validated();
        // try {
            $enrollment = $this->service->createEnrollment($data);
            return redirect()->route('matriculas.index')->with('success', 'Matrícula criada com sucesso!');
        // } catch (\Exception $e) {
        //     return back()->with('error', $e->getMessage());
        // }
    }

    public function cancel(Request $request, $id)
    {
        $reason = $request->input('reason');
        // try {
            $enrollment = $this->service->cancelEnrollment($id, $reason);
            return redirect()->route('matriculas.index')->with('success', 'Matrícula cancelada com sucesso!');
        // } catch (\Exception $e) {
        //     return back()->with('error', $e->getMessage());
        // }
    }

    public function changeClassroom(Request $request, $id)
    {
        $newClassroomId = $request->input('classroom_id');
        // try {
            $enrollment = $this->service->changeClassroom($id, $newClassroomId);
            return redirect()->route('matriculas.index')->with('success', 'Turma alterada com sucesso!');
        // } catch (\Exception $e) {
        //     return back()->with('error', $e->getMessage());
        // }
    }

    public function update(EnrollmentRequest $request, $id)
    {
        $data = $request->validated();
        // try {
            $enrollment = $this->service->updateEnrollment($id, $data);
            return redirect()->route('matriculas.index')->with('success', 'Matrícula atualizada com sucesso!');
        // } catch (\Exception $e) {
        //     return back()->with('error', $e->getMessage());
        // }
    }
} 