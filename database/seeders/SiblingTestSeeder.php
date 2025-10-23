<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiblingGroup;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Classroom;

class SiblingTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar grupos de irmãos
        $this->createSiblingGroups();
        
        // Vincular responsáveis aos grupos
        $this->linkGuardiansToGroups();
        
        // Criar matrículas para testar irmãos ativos
        $this->createTestEnrollments();
    }

    private function createSiblingGroups()
    {
        $groups = [
            [
                'name' => 'Família Silva',
                'description' => 'Família Silva - 3 irmãos',
                'is_active' => true
            ],
            [
                'name' => 'Família Santos',
                'description' => 'Família Santos - 2 irmãos',
                'is_active' => true
            ],
            [
                'name' => 'Família Oliveira',
                'description' => 'Família Oliveira - 2 irmãos',
                'is_active' => true
            ],
            [
                'name' => 'Família Costa',
                'description' => 'Família Costa - 1 irmão',
                'is_active' => true
            ]
        ];

        foreach ($groups as $group) {
            SiblingGroup::firstOrCreate(
                ['name' => $group['name']],
                $group
            );
        }
    }

    private function linkGuardiansToGroups()
    {
        $guardians = Guardian::all();
        $groups = SiblingGroup::all();

        // Família Silva - 3 irmãos
        $silvaGroup = $groups->where('name', 'Família Silva')->first();
        if ($silvaGroup && $guardians->count() >= 3) {
            $silvaGuardians = $guardians->take(3);
            foreach ($silvaGuardians as $guardian) {
                $guardian->update(['sibling_group_id' => $silvaGroup->id]);
            }
        }

        // Família Santos - 2 irmãos
        $santosGroup = $groups->where('name', 'Família Santos')->first();
        if ($santosGroup && $guardians->count() >= 5) {
            $santosGuardians = $guardians->skip(3)->take(2);
            foreach ($santosGuardians as $guardian) {
                $guardian->update(['sibling_group_id' => $santosGroup->id]);
            }
        }

        // Família Oliveira - 2 irmãos
        $oliveiraGroup = $groups->where('name', 'Família Oliveira')->first();
        if ($oliveiraGroup && $guardians->count() >= 7) {
            $oliveiraGuardians = $guardians->skip(5)->take(2);
            foreach ($oliveiraGuardians as $guardian) {
                $guardian->update(['sibling_group_id' => $oliveiraGroup->id]);
            }
        }

        // Família Costa - 1 irmão (sem grupo ainda)
        $costaGroup = $groups->where('name', 'Família Costa')->first();
        if ($costaGroup && $guardians->count() >= 8) {
            $costaGuardian = $guardians->skip(7)->first();
            if ($costaGuardian) {
                $costaGuardian->update(['sibling_group_id' => $costaGroup->id]);
            }
        }
    }

    private function createTestEnrollments()
    {
        $guardians = Guardian::whereNotNull('sibling_group_id')->get();
        $classrooms = Classroom::all();

        foreach ($guardians as $guardian) {
            // Criar alunos para os responsáveis
            $students = Student::whereHas('guardians', function ($query) use ($guardian) {
                $query->where('guardian_id', $guardian->id);
            })->get();

            foreach ($students as $student) {
                // Criar matrícula ativa para alguns alunos
                if (rand(0, 1)) { // 50% de chance
                    $classroom = $classrooms->random();
                    
                    Enrollment::firstOrCreate([
                        'student_id' => $student->id,
                        'guardian_id' => $guardian->id,
                        'classroom_id' => $classroom->id,
                    ], [
                        'status' => 'active',
                        'process' => 'completa',
                        'enrollment_date' => now()->subDays(rand(1, 30)),
                        'notes' => 'Matrícula de teste para sistema de irmãos'
                    ]);
                }
            }
        }
    }
}
