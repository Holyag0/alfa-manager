<?php
// app/Http/Requests/UpdateClassroomRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Classroom;

class UpdateClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_level' => 'required|string|max:10',
            'section' => 'required|string|max:5',
            'school_year' => 'required|integer|min:2020|max:2030',
            'max_students' => 'required|integer|min:5|max:50',
            'teacher_id' => 'nullable|exists:users,id',
            'room_number' => 'nullable|string|max:10',
            'status' => 'required|in:active,inactive',
            
            // Horários
            'schedule' => 'nullable|array',
            'schedule.monday' => 'nullable|array',
            'schedule.monday.*' => 'nullable|string|regex:/^\d{2}:\d{2}-\d{2}:\d{2}$/',
            'schedule.tuesday' => 'nullable|array',
            'schedule.tuesday.*' => 'nullable|string|regex:/^\d{2}:\d{2}-\d{2}:\d{2}$/',
            'schedule.wednesday' => 'nullable|array',
            'schedule.wednesday.*' => 'nullable|string|regex:/^\d{2}:\d{2}-\d{2}:\d{2}$/',
            'schedule.thursday' => 'nullable|array',
            'schedule.thursday.*' => 'nullable|string|regex:/^\d{2}:\d{2}-\d{2}:\d{2}$/',
            'schedule.friday' => 'nullable|array',
            'schedule.friday.*' => 'nullable|string|regex:/^\d{2}:\d{2}-\d{2}:\d{2}$/',
            'schedule.saturday' => 'nullable|array',
            'schedule.saturday.*' => 'nullable|string|regex:/^\d{2}:\d{2}-\d{2}:\d{2}$/',
        ];
    }

    public function messages(): array
    {
        return [
            'grade_level.required' => 'A série/ano é obrigatória.',
            'section.required' => 'A seção (turma) é obrigatória.',
            'school_year.required' => 'O ano letivo é obrigatório.',
            'max_students.required' => 'A capacidade máxima é obrigatória.',
            'max_students.min' => 'A capacidade deve ser pelo menos 5 alunos.',
            'status.required' => 'O status da turma é obrigatório.',
            'status.in' => 'Status inválido.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $classroom = $this->route('classroom');
            
            // Verificar se o novo nome já existe (se mudou)
            $newName = Classroom::generateName($this->grade_level, $this->section);
            
            if ($newName !== $classroom->name) {
                $exists = Classroom::where('name', $newName)
                    ->where('school_year', $this->school_year)
                    ->where('id', '!=', $classroom->id)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('section', 'Já existe uma turma ' . $newName . ' no ano ' . $this->school_year);
                }
            }

            // Verificar se a nova capacidade não é menor que o número atual de alunos
            if ($this->max_students < $classroom->current_students) {
                $validator->errors()->add('max_students', 
                    'A capacidade não pode ser menor que o número atual de alunos (' . $classroom->current_students . ').');
            }

            // Verificar conflito de horário do professor
            if ($this->teacher_id && $this->schedule) {
                $conflictingClassrooms = Classroom::where('teacher_id', $this->teacher_id)
                    ->where('school_year', $this->school_year)
                    ->where('status', 'active')
                    ->where('id', '!=', $classroom->id)
                    ->get();

                foreach ($conflictingClassrooms as $conflictClassroom) {
                    if ($this->hasScheduleConflict($conflictClassroom->schedule, $this->schedule)) {
                        $validator->errors()->add('teacher_id', 
                            'Este professor já tem conflito de horário com a turma ' . $conflictClassroom->name);
                        break;
                    }
                }
            }
        });
    }

    private function hasScheduleConflict($schedule1, $schedule2): bool
    {
        if (!$schedule1 || !$schedule2) {
            return false;
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        foreach ($days as $day) {
            if (isset($schedule1[$day]) && isset($schedule2[$day])) {
                foreach ($schedule1[$day] as $time1) {
                    foreach ($schedule2[$day] as $time2) {
                        if ($this->timeRangesOverlap($time1, $time2)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    private function timeRangesOverlap($range1, $range2): bool
    {
        if (!$range1 || !$range2) {
            return false;
        }

        [$start1, $end1] = explode('-', $range1);
        [$start2, $end2] = explode('-', $range2);

        return $start1 < $end2 && $start2 < $end1;
    }
}