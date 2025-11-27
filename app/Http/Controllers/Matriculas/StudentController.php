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
        $student = Student::with([
            'guardians.contacts', 
            'guardians.addresses',
            'enrollments.classroom',
            'enrollments.guardian'
        ])->findOrFail($id);
        
        // Verificar possibilidade de desconto por irmão (somente feedback)
        $monthlyFeeService = app(\App\Services\MonthlyFeeService::class);
        $hasSiblingDiscountSuggestion = $student->enrollments->contains(function ($enrollment) use ($monthlyFeeService) {
            return $monthlyFeeService->checkSiblingDiscount($enrollment);
        });
        
        // Buscar parcelas de mensalidades do aluno (apenas não deletadas)
        $installments = \App\Models\MonthlyFeeInstallment::whereHas('monthlyFee', function($query) use ($student) {
            $query->whereHas('enrollment', function($q) use ($student) {
                $q->where('student_id', $student->id);
            })
            ->whereNull('deleted_at'); // Apenas contratos não deletados
        })
        ->whereNull('deleted_at') // Apenas parcelas não deletadas
        ->with([
            'monthlyFee.enrollment.classroom',
            'classroomService.service',
            'payments.guardian'
        ])
        ->orderBy('due_date', 'asc')
        ->get();
        
        return Inertia::render('Alunos/Edit', [
            'student' => $student,
            'guardians' => $student->guardians,
            'installments' => $installments,
            'hasSiblingDiscountSuggestion' => $hasSiblingDiscountSuggestion,
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