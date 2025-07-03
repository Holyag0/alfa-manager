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
            UsersTableSeeder::class 
        ]);
        //criando um user isolado  para teste
        User::factory()->withPersonalTeam()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        \App\Models\Student::factory(20)->create();
        // Criar 10 guardians, cada um com 2 contatos (phone e email)
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
        \App\Models\Classroom::factory(5)->create();
        \App\Models\Enrollment::factory(30)->create();
    }
}
