<?php
// app/Services/Contracts/StudentServiceInterface.php

namespace App\Services\Contracts;

use App\Models\Student;
use Illuminate\Pagination\LengthAwarePaginator;

interface StudentServiceInterface
{
    public function getStudentsList(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function findStudentWithRelations(int $studentId): Student;
    public function createStudent(array $data): Student;
    public function updateStudent(Student $student, array $data): Student;
    public function deleteStudent(Student $student): bool;
    public function transferStudent(Student $student, int $newClassroomId, ?string $transferDate = null): bool;
    public function toggleStudentStatus(Student $student): Student;
    public function getStudentsStats(): array;
}
