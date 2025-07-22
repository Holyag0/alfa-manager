<?php

namespace App\Services;

use App\Models\Student;

class ServiceStudent
{
    public function create(array $data)
    {
        $photoPath = null;
        if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
            $photoPath = $data['photo']->store('students', 'public');
            $data['photo'] = $photoPath;
        }
        $student = Student::create($data);

        // Contatos (opcional)
        if (!empty($data['contacts']) && is_array($data['contacts'])) {
            foreach ($data['contacts'] as $contact) {
                if (!empty(array_filter($contact))) {
                    $student->contacts()->create($contact);
                }
            }
        }

        return $student;
    }

    public function update(Student $student, array $data): Student
    {
        if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
            $photoPath = $data['photo']->store('students', 'public');
            $data['photo'] = $photoPath;
        }
        $student->update($data);
        return $student;
    }

    public function delete(Student $student): bool
    {
        return $student->delete();
    }

    public function find($id): ?Student
    {
        return Student::find($id);
    }

    public function search($term = null)
    {
        return Student::query()
            ->when($term, fn($q) => $q->where('name', 'like', "%$term%"))
            ->limit(10)
            ->get();
    }
} 