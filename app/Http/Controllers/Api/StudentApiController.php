<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;

class StudentApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with([
            'guardians' => function($q) {
                $q->orderBy('created_at', 'asc')->limit(1); // Primeiro responsável
            },
            'enrollments' => function($q) {
                $q->where('status', 'active')
                  ->orWhere('status', 'pending')
                  ->orderBy('created_at', 'desc')
                  ->limit(1); // Matrícula mais recente
            },
            'enrollments.classroom',
            'enrollments.guardian'
        ]);
        
        if ($request->has('q') && $request->q) {
            $q = $request->q;
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('cpf', 'like', "%$q%") ;
            });
        }
        
        $students = $query->limit(10)->get();
        
        // Formatar dados para incluir informações da matrícula e responsável
        return $students->map(function($student) {
            $activeEnrollment = $student->enrollments->first();
            $mainGuardian = $student->guardians->first() ?? ($activeEnrollment ? $activeEnrollment->guardian : null);
            
            $classroomName = null;
            if ($activeEnrollment && $activeEnrollment->classroom) {
                $classroomName = $activeEnrollment->classroom->name;
            }
            
            return [
                'id' => $student->id,
                'name' => $student->name,
                'cpf' => $student->cpf,
                'email' => $student->email,
                'phone' => $student->phone,
                'birth_date' => $student->birth_date,
                'photo' => $student->photo,
                'main_guardian_name' => $mainGuardian ? $mainGuardian->name : null,
                'classroom_name' => $classroomName ? $classroomName : 'Não vinculado',
                'enrollment_status' => $activeEnrollment ? $activeEnrollment->status : null,
                'academic_year' => $activeEnrollment ? $activeEnrollment->academic_year : null,
            ];
        });
    }

    public function show($id)
    {
        $student = Student::with(['guardians', 'enrollments.classroom'])->findOrFail($id);
        return response()->json($student);
    }

    public function guardians($id)
    {
        $student = Student::findOrFail($id);
        return response()->json($student->guardians);
    }
} 