<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Resetar o cache de permissões/roles
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Criar permissões básicas
        $permissions = [
            'view users',
            'edit users',
            'delete users',
            'create users',
            'view articles',
            'edit articles',
            'delete articles',
            'create articles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Criar papel de administrador e atribuir todas as permissões
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // Criar papel de secretário e atribuir permissões específicas
        $secretaryRole = Role::firstOrCreate(['name' => 'secretario']);
        $secretaryPermissions = ['view users', 'view articles'];
        foreach ($secretaryPermissions as $permission) {
            $secretaryRole->givePermissionTo($permission);
        }
    }
}