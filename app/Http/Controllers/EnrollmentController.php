<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnrollmentRequest;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    protected $service;

    public function __construct(EnrollmentService $service)
    {
        $this->service = $service;
    }

    public function store(EnrollmentRequest $request)
    {
        $data = $request->validated();
        // try {
            $enrollment = $this->service->createEnrollment($data);
            return response()->json(['success' => true, 'enrollment' => $enrollment], 201);
        // } catch (\Exception $e) {
        //     return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        // }
    }

    public function cancel(Request $request, $id)
    {
        $reason = $request->input('reason');
        // try {
            $enrollment = $this->service->cancelEnrollment($id, $reason);
            return response()->json(['success' => true, 'enrollment' => $enrollment]);
        // } catch (\Exception $e) {
        //     return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        // }
    }

    public function changeClassroom(Request $request, $id)
    {
        $newClassroomId = $request->input('classroom_id');
        // try {
            $enrollment = $this->service->changeClassroom($id, $newClassroomId);
            return response()->json(['success' => true, 'enrollment' => $enrollment]);
        // } catch (\Exception $e) {
        //     return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        // }
    }

    public function index(Request $request)
    {
        $filters = $request->only(['student_id', 'guardian_id', 'classroom_id', 'status']);
        $enrollments = $this->service->searchEnrollments($filters);
        return response()->json(['success' => true, 'enrollments' => $enrollments]);
    }
} 