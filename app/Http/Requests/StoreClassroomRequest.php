<?php
// app/Http/Requests/StoreClassroomRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Classroom;

class StoreClassroomRequest extends FormRequest
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
            
            // Horários opcionais
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
            'school_year.min' => 'Ano letivo deve ser maior que 2020.',
            'school_year.max' => 'Ano letivo deve ser menor que 2030.',
            'max_students.required' => 'A capacidade máxima é obrigatória.',
            'max_students.min' => 'A capacidade deve ser pelo menos 5 alunos.',
            'max_students.max' => 'A capacidade não pode exceder 50 alunos.',
            'teacher_id.exists' => 'O professor selecionado não existe.',
            'schedule.*.*.regex' => 'Formato de horário inválido. Use: HH:MM-HH:MM (ex: 08:00-11:30)',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Verificar se já existe uma turma com o mesmo nome no ano
            $name = Classroom::generateName($this->grade_level, $this->section);
            
            $exists = Classroom::where('name', $name)
                ->where('school_year', $this->school_year)
                ->exists();

            if ($exists) {
                $validator->errors()->add('section', 'Já existe uma turma ' . $name . ' no ano ' . $this->school_year);
            }

            // Verificar se o professor já está responsável por outra turma no mesmo horário
            if ($this->teacher_id && $this->schedule) {
                $conflictingClassrooms = Classroom::where('teacher_id', $this->teacher_id)
                    ->where('school_year', $this->school_year)
                    ->where('status', 'active')
                    ->get();

                foreach ($conflictingClassrooms as $classroom) {
                    if ($this->hasScheduleConflict($classroom->schedule, $this->schedule)) {
                        $validator->errors()->add('teacher_id', 'Este professor já tem conflito de horário com a turma ' . $classroom->name);
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
