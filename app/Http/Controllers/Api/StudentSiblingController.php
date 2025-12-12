<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\ServiceStudent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentSiblingController extends Controller
{
    protected ServiceStudent $service;

    public function __construct(ServiceStudent $service)
    {
        $this->service = $service;
    }

    /**
     * Obter irmãos de um aluno
     */
    public function index(Student $student): JsonResponse
    {
        $siblings = $this->service->getSiblings($student);
        
        return response()->json($siblings->map(function ($sibling) {
            $activeEnrollment = $sibling->enrollments->where('status', 'active')->first();
            
            return [
                'id' => $sibling->id,
                'name' => $sibling->name,
                'cpf' => $sibling->cpf,
                'birth_date' => $sibling->birth_date,
                'photo' => $sibling->photo,
                'classroom_name' => $activeEnrollment && $activeEnrollment->classroom 
                    ? $activeEnrollment->classroom->name 
                    : null,
                'enrollment_status' => $activeEnrollment ? $activeEnrollment->status : null,
            ];
        }));
    }

    /**
     * Adicionar irmão ao aluno
     */
    public function store(Request $request, Student $student): JsonResponse
    {
        $request->validate([
            'sibling_id' => 'required|exists:students,id|different:' . $student->id,
        ]);

        try {
            $sibling = Student::findOrFail($request->sibling_id);
            $group = $this->service->addSibling($student, $sibling);
            
            return response()->json([
                'message' => 'Irmão adicionado com sucesso!',
                'sibling' => [
                    'id' => $sibling->id,
                    'name' => $sibling->name,
                ],
                'group' => $group
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remover vínculo de irmão
     */
    public function destroy(Student $student, Student $sibling): JsonResponse
    {
        try {
            $this->service->removeSibling($student, $sibling);
            
            return response()->json([
                'message' => 'Vínculo de irmão removido com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}

