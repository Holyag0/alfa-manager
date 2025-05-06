<?php
namespace App\Providers;

use App\Actions\Jetstream\AddTeamMember;
use App\Actions\Jetstream\CreateTeam;
use App\Actions\Jetstream\DeleteTeam;
use App\Actions\Jetstream\DeleteUser;
use App\Actions\Jetstream\InviteTeamMember;
use App\Actions\Jetstream\RemoveTeamMember;
use App\Actions\Jetstream\UpdateTeamName;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePermissions();

        Jetstream::createTeamsUsing(CreateTeam::class);
        Jetstream::updateTeamNamesUsing(UpdateTeamName::class);
        Jetstream::addTeamMembersUsing(AddTeamMember::class);
        Jetstream::inviteTeamMembersUsing(InviteTeamMember::class);
        Jetstream::removeTeamMembersUsing(RemoveTeamMember::class);
        Jetstream::deleteTeamsUsing(DeleteTeam::class);
        Jetstream::deleteUsersUsing(DeleteUser::class);
    }

    /**
     * Configure the roles and permissions that are available within the application.
     */
    protected function configurePermissions(): void
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        // Integrate Spatie Laravel Permission roles
        $this->createSpatieRolesAndPermissions();
    }

    /**
     * Create roles and permissions using Spatie Laravel Permission.
     */
    protected function createSpatieRolesAndPermissions()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'create', 'read', 'update', 'delete',
            'create finances', 'read finances', 'update finances', 'delete finances',
            'create secretariat', 'read secretariat', 'update secretariat', 'delete secretariat',
            'create board', 'read board', 'update board', 'delete board'
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign existing permissions
        $financePermissions = ['create finances', 'read finances', 'update finances', 'delete finances'];
        $secretariatPermissions = ['create secretariat', 'read secretariat', 'update secretariat', 'delete secretariat'];
        $boardPermissions = ['create board', 'read board', 'update board', 'delete board'];

        $role = Role::firstOrCreate(['name' => 'financeiro']);
        $role->syncPermissions($financePermissions);

        $role = Role::firstOrCreate(['name' => 'secretaria']);
        $role->syncPermissions($secretariatPermissions);

        $role = Role::firstOrCreate(['name' => 'diretoria']);
        $role->syncPermissions($boardPermissions);

        // Create admin and developer roles with all permissions
        $adminPermissions = ['create', 'read', 'update', 'delete'];
        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->syncPermissions(array_merge($adminPermissions, $financePermissions, $secretariatPermissions, $boardPermissions));

        $role = Role::firstOrCreate(['name' => 'developer']);
        $role->syncPermissions(array_merge($adminPermissions, $financePermissions, $secretariatPermissions, $boardPermissions));
    }
}