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
            'create finances', 'read finances', 'update finances', 'delete finances',
            'create secretariat', 'read secretariat', 'update secretariat', 'delete secretariat',
            'create board', 'read board', 'update board', 'delete board'
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Criar roles e atribuir permissões existentes
        $financePermissions = ['create finances', 'read finances', 'update finances', 'delete finances'];
        $secretariatPermissions = ['create secretariat', 'read secretariat', 'update secretariat', 'delete secretariat'];
        $boardPermissions = ['create board', 'read board', 'update board', 'delete board'];

        $role = Role::firstOrCreate(['name' => 'financeiro']);
        $role->syncPermissions($financePermissions);

        $role = Role::firstOrCreate(['name' => 'secretaria']);
        $role->syncPermissions($secretariatPermissions);

        $role = Role::firstOrCreate(['name' => 'diretoria']);
        $role->syncPermissions($boardPermissions);

        // Criar role admin e atribuir permissões
        $adminPermissions = ['create', 'read', 'update', 'delete'];
        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->syncPermissions(array_merge($adminPermissions, $financePermissions, $secretariatPermissions, $boardPermissions));

        // Criar role developer com permissões de admin
        $role = Role::firstOrCreate(['name' => 'developer']);
        $role->syncPermissions(array_merge($adminPermissions, $financePermissions, $secretariatPermissions, $boardPermissions));
    }
}
