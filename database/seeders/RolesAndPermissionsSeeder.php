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
            'financeiro-create', 'financeiro-read', 'financeiro-update','financeiro-delete',
            'aluno-create', 'aluno-read', 'aluno-update', 'aluno-delete',
            'role-create', 'role-read', 'role-update', 'role-delete',
            'permission-create','permission-read','permission-update','permission-delete'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
        // Criar roles e atribuir permissões existentes
        $userPermissions = ['user-create', 'user-read', 'user-update', 'user-delete'];
        $financePermissions = ['financeiro-create', 'financeiro-read', 'financeiro-update', 'financeiro-delete'];
        $rolePermissions = ['role-create', 'role-read', 'role-update', 'role-delete'];
        $alunosPermissions = ['aluno-create', 'aluno-read', 'aluno-update', 'aluno-delete'];
        $crudPermissions =['permission-create','permission-read','permission-update','permission-delete'];

        $role = Role::firstOrCreate(['name' => 'financeiro', 'guard_name' => 'web']);
        $role->syncPermissions($financePermissions);

        $role = Role::firstOrCreate(['name' => 'secretaria', 'guard_name' => 'web']);
        $role->syncPermissions($rolePermissions);

        $role = Role::firstOrCreate(['name' => 'diretoria', 'guard_name' => 'web']);
        $role->syncPermissions($alunosPermissions);

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $role->syncPermissions(array_merge($crudPermissions,$userPermissions,$financePermissions, $rolePermissions, $alunosPermissions));

        $role = Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']);
        $role->syncPermissions(array_merge($crudPermissions,$userPermissions, $financePermissions, $rolePermissions, $alunosPermissions));
    }
}

