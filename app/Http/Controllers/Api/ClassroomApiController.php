<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classroom;

class ClassroomApiController extends Controller
{
    public function index()
    {
        return Classroom::all();
    }
} 