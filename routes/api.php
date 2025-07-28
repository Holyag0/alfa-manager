<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GuardianApiController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\ClassroomApiController;
use App\Http\Controllers\Matriculas\GuardianController;
use App\Http\Controllers\Matriculas\StudentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('guardians', [GuardianApiController::class, 'index']);
Route::get('students', [StudentApiController::class, 'index']);
Route::get('classrooms', [ClassroomApiController::class, 'index']);

// Rotas de CRUD e autocomplete para Guardian
Route::apiResource('guardians', GuardianController::class);
Route::get('guardians-autocomplete', [GuardianController::class, 'autocomplete']);

// Rotas de CRUD e autocomplete para Student
Route::apiResource('students', StudentController::class);
Route::get('students-autocomplete', [StudentController::class, 'autocomplete']);

// Rota API para buscar guardians não vinculados a um aluno
Route::get('students/{student}/guardians/search-not-linked', [GuardianController::class, 'searchNotLinked']);
