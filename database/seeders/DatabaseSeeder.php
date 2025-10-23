<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->withPersonalTeam()->create();
        $this->call([
            RolesAndPermissionsSeeder::class,
            UsersTableSeeder::class,
            ComercialSeeder::class,
            PromotionTestSeeder::class,
            SiblingTestSeeder::class
        ]);
        //criando um user isolado  para teste
        User::factory()->withPersonalTeam()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        
        // Criar estudantes
        \App\Models\Student::factory(20)->create();
        
        // Criar responsáveis
        \App\Models\Guardian::factory(10)->create()->each(function($guardian) {
            \App\Models\Contact::factory()->create([
                'guardian_id' => $guardian->id,
                'type' => 'phone',
                'value' => fake()->phoneNumber(),
                'label' => 'pessoal',
                'is_primary' => true,
                'contact_for' => 'pessoal',
            ]);
            \App\Models\Contact::factory()->create([
                'guardian_id' => $guardian->id,
                'type' => 'email',
                'value' => fake()->unique()->safeEmail(),
                'label' => 'pessoal',
                'is_primary' => false,
                'contact_for' => 'pessoal',
            ]);
        });
        
        // Criar salas
        \App\Models\Classroom::factory(5)->create();
        
        // Vincular responsáveis aos alunos (guardian_student)
        $students = \App\Models\Student::all();
        $guardians = \App\Models\Guardian::all();
        
        foreach ($students as $student) {
            // Cada aluno terá de 1 a 3 responsáveis
            $randomGuardians = $guardians->random(rand(1, 3));
            
            foreach ($randomGuardians as $guardian) {
                // Evitar duplicatas
                if (!$student->guardians()->where('guardian_id', $guardian->id)->exists()) {
                    $student->guardians()->attach($guardian->id, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        
        // Criar matrículas - agora que já temos alunos e responsáveis vinculados
        foreach ($students as $student) {
            // Cada aluno pode ter de 0 a 2 matrículas
            $numEnrollments = rand(0, 2);
            
            for ($i = 0; $i < $numEnrollments; $i++) {
                // Pegar um responsável vinculado a este aluno
                $guardian = $student->guardians()->inRandomOrder()->first();
                
                if ($guardian) {
                    \App\Models\Enrollment::create([
                        'student_id' => $student->id,
                        'guardian_id' => $guardian->id,
                        'classroom_id' => \App\Models\Classroom::inRandomOrder()->first()->id,
                        'status' => fake()->randomElement(['active', 'pending', 'cancelled']),
                        'enrollment_date' => fake()->date('Y-m-d'),
                        'notes' => fake()->optional()->sentence(),
                    ]);
                }
            }
        }
    }
}
