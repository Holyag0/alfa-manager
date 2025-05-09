<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Resetar roles e permissões em cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Criar permissões
        $permissions = [
            'user-create', 'user-read', 'user-update', 'user-delete',
            'create-finances', 'read-finances', 'update-finances', 'delete-finances',
            'create-secretariat', 'read-secretariat', 'update-secretariat', 'delete-secretariat',
            'create-board', 'read-board', 'update-board', 'delete-board'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Criar roles e atribuir permissões existentes
        $financePermissions = ['create-finances', 'read-finances', 'update-finances', 'delete-finances'];
        $secretariatPermissions = ['create-secretariat', 'read-secretariat', 'update-secretariat', 'delete-secretariat'];
        $boardPermissions = ['create-board', 'read-board', 'update-board', 'delete-board'];
        $userPermissions = ['user-create', 'user-read', 'user-update', 'user-delete'];

        $role = Role::firstOrCreate(['name' => 'financeiro', 'guard_name' => 'web']);
        $role->syncPermissions($financePermissions);

        $role = Role::firstOrCreate(['name' => 'secretaria', 'guard_name' => 'web']);
        $role->syncPermissions($secretariatPermissions);

        $role = Role::firstOrCreate(['name' => 'diretoria', 'guard_name' => 'web']);
        $role->syncPermissions($boardPermissions);

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $role->syncPermissions(array_merge($userPermissions, $financePermissions, $secretariatPermissions, $boardPermissions));

        $role = Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']);
        $role->syncPermissions(array_merge($userPermissions, $financePermissions, $secretariatPermissions, $boardPermissions));
    }
}

