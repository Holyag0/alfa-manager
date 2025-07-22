<?php

namespace App\Http\Controllers\Matriculas;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\Student;
use App\Services\ServiceStudent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    protected ServiceStudent $service;

    public function __construct(ServiceStudent $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $students = $this->service->search($request->input('q'));
        return response()->json($students);
    }

    public function store(StudentRequest $request)
    {
       
        $data = $request->validated();
        $student = $this->service->create($data);
        $student = $this->service->find($student->id);
        return redirect()->back()->with([
            'success' => 'Aluno cadastrado com sucesso!',
            'student' => $student ? $student->toArray() : null
        ]);
    }

    public function show($id)
    {
        $student = $this->service->find($id);
        if (!$student) return response()->json(['error' => 'Not found'], 404);
        return response()->json($student);
    }

    public function edit($id)
    {
        $student = Student::with(['guardians.contacts', 'guardians.addresses'])->findOrFail($id);
        return Inertia::render('Alunos/Edit', [
            'student' => $student,
            'guardians' => $student->guardians
        ]);
    }

    public function update(StudentRequest $request, $id)
    {
        $student = $this->service->find($id);
        if (!$student) {
            return redirect()->route('students.index')->withErrors(['error' => 'Aluno não encontrado']);
        }
        
        $this->service->update($student, $request->validated());
        
        return redirect()->back()->with('success', 'Aluno atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $student = $this->service->find($id);
        if (!$student) return response()->json(['error' => 'Not found'], 404);
        $this->service->delete($student);
        return response()->json(['success' => true]);
    }

    public function autocomplete(Request $request)
    {
        $results = $this->service->search($request->input('q'));
        return response()->json($results);
    }
} 