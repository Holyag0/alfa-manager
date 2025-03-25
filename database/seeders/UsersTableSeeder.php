<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Cria um usuário administrador
        $adminUser = User::create([
            'name' => 'Hiago santos',
            'email' => 'masterDev@gmail.com',
            'password' => bcrypt('alfa!%$&1547')
        ]);
        
        // Cria um usuário secretário
        $secretaryUser = User::create([
            'name' => 'Secretário Exemplo',
            'email' => 'secretario@example.com',
            'password' => bcrypt('password')
        ]);
    }
}