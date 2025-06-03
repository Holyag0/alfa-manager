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
            GuardianSeeder::class,
            ClassroomSeeder::class,
            StudentSeeder::class,
        ]);
        //criando um user isolado  para teste
        User::factory()->withPersonalTeam()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $this->command->info('🎉 Seeding concluído com sucesso!');
        $this->command->info('');
        $this->command->info('📋 Resumo dos dados criados:');
        $this->command->info('👥 Responsáveis: ' . \App\Models\Guardian::count());
        $this->command->info('🏫 Turmas: ' . \App\Models\Classroom::count());
        $this->command->info('🎓 Alunos: ' . \App\Models\Student::count());
        $this->command->info('📚 Matrículas: ' . \App\Models\Enrollment::count());
        $this->command->info('');
        $this->command->info('🔐 Para testar, use os dados:');
        $this->command->info('📧 Responsável: maria.silva@email.com');
        $this->command->info('📧 Responsável: joao.carlos@email.com');
        $this->command->info('🎓 Aluno de teste: Pedro Silva Santos (Matrícula: ' . now()->year . '999)');
    }
}
