<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;

class StudentApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query();
        if ($request->has('q') && $request->q) {
            $q = $request->q;
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('cpf', 'like', "%$q%") ;
            });
        }
        return $query->limit(10)->get();
    }
} 