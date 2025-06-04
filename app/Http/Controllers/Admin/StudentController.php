<?php
// app/Http/Controllers/Admin/StudentController.php (atualizado para usar Services)

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StudentService;
use App\Services\ClassroomService;
use App\Services\GuardianService;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    private $studentService;
    private $classroomService;
    private $guardianService;

    public function __construct(StudentService $studentService, ClassroomService $classroomService, GuardianService $guardianService)
    {
        $this->studentService = $studentService;
        $this->classroomService = $classroomService;
        $this->guardianService = $guardianService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'classroom_id', 'grade']);
        $students = $this->studentService->getStudentsList($filters);

        return Inertia::render('Admin/Students/Index', [
            'students' => $students,
            'classrooms' => $this->classroomService->getAvailableClassrooms(),
            'grades' => $this->classroomService->getAvailableGrades(),
            'stats' => $this->studentService->getStudentsStats(),
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Students/Create', [
            'classrooms' => $this->classroomService->getAvailableClassrooms(),
            'guardians' => $this->guardianService->searchGuardiansForApi('', 100),
        ]);
    }

    public function store(StoreStudentRequest $request)
    {
        try {
            $student = $this->studentService->createStudent($request->all());

            return redirect()->route('admin.students.show', $student)
                ->with('success', 'Aluno cadastrado com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Student $student)
    {
        $student = $this->studentService->findStudentWithRelations($student->id);
        $enrollmentHistory = $this->studentService->getStudentEnrollmentHistory($student);

        return Inertia::render('Admin/Students/Show', [
            'student' => $student,
            'enrollmentHistory' => $enrollmentHistory,
        ]);
    }

    public function edit(Student $student)
    {
        $student = $this->studentService->findStudentWithRelations($student->id);

        return Inertia::render('Admin/Students/Edit', [
            'student' => $student,
            'classrooms' => $this->classroomService->getAvailableClassrooms(),
            'guardians' => $this->guardianService->searchGuardiansForApi('', 100),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        try {
            $student = $this->studentService->updateStudent($student, $request->validated());

            return redirect()->route('admin.students.show', $student)
                ->with('success', 'Aluno atualizado com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Student $student)
    {
        try {
            $this->studentService->deleteStudent($student);

            return redirect()->route('admin.students.index')
                ->with('success', 'Aluno removido com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function transfer(Request $request, Student $student)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'transfer_date' => 'required|date',
        ]);

        try {
            $this->studentService->transferStudent(
                $student, 
                $request->classroom_id, 
                $request->transfer_date
            );

            return back()->with('success', 'Aluno transferido com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function toggleStatus(Student $student)
    {
        try {
            $student = $this->studentService->toggleStudentStatus($student);
            
            $message = $student->status === 'active' 
                ? 'Aluno ativado com sucesso!' 
                : 'Aluno inativado com sucesso!';

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
