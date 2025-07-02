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
        \App\Models\Guardian::factory(10)->create();
        \App\Models\Classroom::factory(5)->create();
        \App\Models\Enrollment::factory(30)->create();
    }
}
