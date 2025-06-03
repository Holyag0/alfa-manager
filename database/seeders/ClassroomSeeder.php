<?php
// database/seeders/ClassroomSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classroom;
use App\Models\User;

class ClassroomSeeder extends Seeder
{
    public function run()
    {
        // Busca alguns usuários para serem professores (ou cria um se não existir)
        $teachers = User::where('is_auth', 1)->take(10)->get();
        
        if ($teachers->isEmpty()) {
            // Cria um professor básico se não existir
            $teacher = User::create([
                'name' => 'Professor Demo',
                'email' => 'professor@escola.com',
                'password' => bcrypt('password'),
                'is_auth' => 1,
            ]);
            $teachers = collect([$teacher]);
        }

        $currentYear = now()->year;
        $grades = ['1°', '2°', '3°', '4°', '5°', '6°', '7°', '8°', '9°'];
        $sections = ['A', 'B', 'C'];
        $roomNumber = 101;

        foreach ($grades as $grade) {
            foreach ($sections as $section) {
                $name = $grade . $section;
                
                // Algumas turmas podem não ter professor ainda
                $teacher = $teachers->random();
                
                Classroom::create([
                    'name' => $name,
                    'grade_level' => $grade,
                    'section' => $section,
                    'school_year' => $currentYear,
                    'max_students' => rand(25, 35),
                    'current_students' => 0, // Será atualizado quando matricular alunos
                    'teacher_id' => rand(1, 3) === 1 ? null : $teacher->id, // 1/3 sem professor
                    'room_number' => (string) $roomNumber,
                    'schedule' => [
                        'monday' => ['07:00-11:30'],
                        'tuesday' => ['07:00-11:30'],
                        'wednesday' => ['07:00-11:30'],
                        'thursday' => ['07:00-11:30'],
                        'friday' => ['07:00-11:30'],
                    ],
                    'status' => 'active',
                ]);
                
                $roomNumber++;
            }
        }

        // Criar algumas turmas do ano anterior para histórico
        $lastYear = $currentYear - 1;
        $historicGrades = ['1°', '2°', '3°'];
        
        foreach ($historicGrades as $grade) {
            foreach (['A', 'B'] as $section) {
                $name = $grade . $section;
                
                Classroom::create([
                    'name' => $name,
                    'grade_level' => $grade,
                    'section' => $section,
                    'school_year' => $lastYear,
                    'max_students' => 30,
                    'current_students' => rand(20, 30),
                    'teacher_id' => $teachers->random()->id,
                    'room_number' => (string) $roomNumber,
                    'status' => 'inactive', // Turmas do ano anterior
                ]);
                
                $roomNumber++;
            }
        }

        $this->command->info('✅ Turmas criadas com sucesso!');
    }
}