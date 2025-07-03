<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GuardianApiController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\ClassroomApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('guardians', [GuardianApiController::class, 'index']);
Route::get('students', [StudentApiController::class, 'index']);
Route::get('classrooms', [ClassroomApiController::class, 'index']);
