<?php
// app/Http/Controllers/Admin/ClassroomController.php (atualizado para usar Services)

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ClassroomService;
use App\Services\StudentService;
use App\Http\Requests\StoreClassroomRequest;
use App\Http\Requests\UpdateClassroomRequest;
use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassroomController extends Controller
{
    public function __construct(
        private ClassroomService $classroomService,
        private StudentService $studentService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'grade', 'year', 'status', 'availability']);
        $classrooms = $this->classroomService->getClassroomsList($filters);

        return Inertia::render('Admin/Classrooms/Index', [
            'classrooms' => $classrooms,
            'grades' => $this->classroomService->getAvailableGrades($filters['year'] ?? null),
            'years' => $this->classroomService->getAvailableYears(),
            'stats' => $this->classroomService->getClassroomsStats($filters['year'] ?? null),
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Classrooms/Create', [
            'teachers' => $this->classroomService->getAvailableTeachers(),
            'defaultYear' => now()->month >= 10 ? now()->year + 1 : now()->year,
        ]);
    }

    public function store(StoreClassroomRequest $request)
    {
        try {
            $classroom = $this->classroomService->createClassroom($request->validated());

            return redirect()->route('admin.classrooms.show', $classroom)
                ->with('success', 'Turma criada com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Classroom $classroom)
    {
        $classroom = $this->classroomService->findClassroomWithRelations($classroom->id);
        $availableStudents = $this->studentService->getAvailableStudentsForEnrollment();

        return Inertia::render('Admin/Classrooms/Show', [
            'classroom' => $classroom,
            'availableStudents' => $availableStudents,
        ]);
    }

    public function edit(Classroom $classroom)
    {
        return Inertia::render('Admin/Classrooms/Edit', [
            'classroom' => $classroom,
            'teachers' => $this->classroomService->getAvailableTeachers(),
        ]);
    }

    public function update(UpdateClassroomRequest $request, Classroom $classroom)
    {
        try {
            $classroom = $this->classroomService->updateClassroom($classroom, $request->validated());

            return redirect()->route('admin.classrooms.show', $classroom)
                ->with('success', 'Turma atualizada com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Classroom $classroom)
    {
        try {
            $this->classroomService->deleteClassroom($classroom);

            return redirect()->route('admin.classrooms.index')
                ->with('success', 'Turma removida com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function enrollStudent(Request $request, Classroom $classroom)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'enrollment_date' => 'required|date',
        ]);

        try {
            $this->classroomService->enrollStudent(
                $classroom, 
                $request->student_id, 
                $request->enrollment_date
            );

            return back()->with('success', 'Aluno matriculado com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function removeStudent(Request $request, Classroom $classroom, Student $student)
    {
        $request->validate([
            'reason' => 'required|in:transferred,dropped,completed',
            'end_date' => 'required|date',
        ]);

        try {
            $this->classroomService->removeStudent(
                $classroom, 
                $student, 
                $request->reason, 
                $request->end_date
            );

            return back()->with('success', 'Aluno removido da turma com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function duplicate(Classroom $classroom)
    {
        try {
            $newClassroom = $this->classroomService->duplicateClassroom($classroom);

            return redirect()->route('admin.classrooms.show', $newClassroom)
                ->with('success', 'Turma duplicada com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function report(Classroom $classroom)
    {
        $report = $this->classroomService->generateClassroomReport($classroom);

        return Inertia::render('Admin/Classrooms/Report', $report);
    }

    public function updateStudentCount(Classroom $classroom)
    {
        $classroom = $this->classroomService->updateStudentCount($classroom);

        return back()->with('success', 'Contador de alunos atualizado!');
    }
}
