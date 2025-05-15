<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class ServiceRole
{
  /**
   * @var Role
   */
  private $role;

  /**
   * Constructor with dependency injection
   * 
   * @param Role $role
   */
  public function __construct(Role $role)
  {
    $this->role = $role;
  }

  /**
   * @return Role
   */
  public function getRole()
  {
    return $this->role;
  }

  /**
   * Get all roles with pagination
   * 
   * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
   */
  public function all()
  {
    return $this->getRole()
      ->with('permissions')
      ->orderBy('name')
      ->paginate(10);
  }

  /**
   * Find role by ID with permissions
   * 
   * @param int $id
   * @return Role
   */
  public function show($id)
  {
    return $this->getRole()->with('permissions')->find($id);
  }

  /**
   * Create a new role with permissions
   * 
   * @param array $data
   * @return Role
   */
  public function store($data)
  {
    $role = $this->getRole()->create([
      'name' => $data['name'],
      'guard_name' => 'web'
    ]);

    // Log activity
    // activity()
    //   ->causedBy(auth()->user())
    //   ->performedOn($role)
    //   ->log("ROLE: :subject.name foi criada");

    // Sync permissions
    if (isset($data['permissions'])) {
      $role->syncPermissions($data['permissions']);
    }

    return $role;
  }

  /**
   * Update a role and its permissions
   * 
   * @param int $id
   * @param array $data
   * @return Role
   */
  public function update($id, $data)
  {
    $role = $this->getRole()->find($id);

    if (!$role) {
      return false;
    }

    // Update role name
    $role->name = $data['name'];
    $role->save();

    // Log activity
    // activity()
    //   ->causedBy(auth()->user())
    //   ->performedOn($role)
    //   ->log("ROLE: :subject.name foi atualizada");

    // Sync permissions
    if (isset($data['permissions'])) {
      $role->syncPermissions($data['permissions']);
    }

    return $role;
  }

  /**
   * Delete a role
   * 
   * @param int $id
   * @return bool
   */
  public function destroy($id)
  {
    $role = $this->getRole()->find($id);

    if (!$role) {
      return false;
    }

    // Log activity
    // activity()
    //   ->causedBy(auth()->user())
    //   ->performedOn($role)
    //   ->log("ROLE: :subject.name foi deletada");

    return $role->delete();
  }

  /**
   * Get all permissions
   * 
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function getAllPermissions()
  {
    return Permission::orderBy('name')->get();
  }

  /**
   * Get permissions for a specific role
   * 
   * @param int $roleId
   * @return array
   */
  public function getRolePermissions($roleId)
  {
    return DB::table("role_has_permissions")
      ->where("role_has_permissions.role_id", $roleId)
      ->pluck('role_has_permissions.permission_id')
      ->toArray();
  }
}
