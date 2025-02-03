<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

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
        $adminRole = Role::where('name', 'admin')->first();
        $adminUser->assignRole($adminRole);

        // Cria um usuário secretário
        $secretaryUser = User::create([
            'name' => 'Secretário Exemplo',
            'email' => 'secretario@example.com',
            'password' => bcrypt('password')
        ]);
        $secretaryRole = Role::where('name', 'secretario')->first();
        $secretaryUser->assignRole($secretaryRole);
    }
}