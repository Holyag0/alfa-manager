<?php
// app/Services/Contracts/ClassroomServiceInterface.php

namespace App\Services\Contracts;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\Enrollment;
use Illuminate\Pagination\LengthAwarePaginator;

interface ClassroomServiceInterface
{
    public function getClassroomsList(array $filters = [], int $perPage = 12): LengthAwarePaginator;
    public function findClassroomWithRelations(int $classroomId): Classroom;
    public function createClassroom(array $data): Classroom;
    public function updateClassroom(Classroom $classroom, array $data): Classroom;
    public function deleteClassroom(Classroom $classroom): bool;
    public function enrollStudent(Classroom $classroom, int $studentId, ?string $enrollmentDate = null): Enrollment;
    public function removeStudent(Classroom $classroom, Student $student, string $reason = 'transferred', ?string $endDate = null): bool;
    public function getClassroomsStats(?int $year = null): array;
}
