<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class ClassroomApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Classroom::query();
        
        if ($request->has('q') && !empty($request->q)) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }
        
        $classrooms = $query->get();
        
        return response()->json($classrooms);
    }

    public function getEnrollments($classroomId)
    {
        $enrollmentCount = Enrollment::where('classroom_id', $classroomId)
            ->where('status', '!=', 'cancelled')
            ->count();
            
        return response()->json(['count' => $enrollmentCount]);
    }

    public function show($id)
    {
        $classroom = Classroom::findOrFail($id);
        
        return response()->json([
            'id' => $classroom->id,
            'name' => $classroom->name,
            'year' => $classroom->year,
            'shift' => $classroom->shift,
            'max_students' => $classroom->max_students,
            'current_students' => $classroom->getEnrolledStudentsCount(),
            'available_slots' => $classroom->getAvailableSlots(),
            'is_active' => $classroom->is_active,
            'vacancies' => $classroom->vacancies,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|integer|min:2000|max:' . (now()->year + 5),
            'shift' => 'required|string|in:Manhã,Tarde,Noite,Integral',
            'max_students' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $classroom = Classroom::findOrFail($id);
        $classroom->update($validated);
        
        // Atualizar contador após mudança
        $classroom->updateEnrolledCount();

        return response()->json([
            'message' => 'Turma atualizada com sucesso!',
            'classroom' => [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'year' => $classroom->year,
                'shift' => $classroom->shift,
                'max_students' => $classroom->max_students,
                'current_students' => $classroom->current_students,
                'available_slots' => $classroom->getAvailableSlots(),
                'is_active' => $classroom->is_active,
            ]
        ]);
    }
} 