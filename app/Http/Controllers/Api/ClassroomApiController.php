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
} 