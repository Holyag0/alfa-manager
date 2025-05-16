<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class ServicePermission
{
  /**
   * @var Permission
   */
  private $permission;

  /**
   * Constructor with dependency injection
   * 
   * @param Permission $permission
   */
  public function __construct(Permission $permission)
  {
    $this->permission = $permission;
  }

  /**
   * @return Permission
   */
  public function getPermission()
  {
    return $this->permission;
  }

  /**
   * Get all permissions with pagination
   * 
   * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
   */
  public function all()
  {
    return $this->getPermission()
      ->with('roles')
      ->orderBy('name')
      ->paginate(10);
  }
  

  /**
   * Find permission by ID with roles
   * 
   * @param int $id
   * @return Permission
   */
  public function show($id)
  {
    return $this->getPermission()->with('roles')->find($id);
  }

  /**
   * Create a new permission
   * 
   * @param array $data
   * @return Permission
   */
  public function store($data)
  {
    $permission = $this->getPermission()->create([
      'name' => $data['name'],
      'guard_name' => 'web'
    ]);

    // Log activity
    // activity()
    //   ->causedBy(auth()->user())
    //   ->performedOn($permission)
    //   ->log("PERMISSÃO: :subject.name foi criada");

    return $permission;
  }

  /**
   * Update a permission
   * 
   * @param int $id
   * @param array $data
   * @return Permission
   */
  public function update($id, $data)
  {
    $permission = $this->getPermission()->find($id);

    if (!$permission) {
      return false;
    }

    // Update permission name
    $permission->name = $data['name'];
    $permission->save();

    // Log activity
    // activity()
    //   ->causedBy(auth()->user())
    //   ->performedOn($permission)
    //   ->log("PERMISSÃO: :subject.name foi atualizada");

    return $permission;
  }

  /**
   * Delete a permission
   * 
   * @param int $id
   * @return bool
   */
  public function destroy($id)
  {
    $permission = $this->getPermission()->find($id);

    if (!$permission) {
      return false;
    }

    // Log activity
    // activity()
    //   ->causedBy(auth()->user())
    //   ->performedOn($permission)
    //   ->log("PERMISSÃO: :subject.name foi deletada");

    return $permission->delete();
  }

  /**
   * Get roles associated with a permission
   * 
   * @param int $permissionId
   * @return array
   */
  public function getPermissionRoles($permissionId)
  {
    return DB::table("role_has_permissions")
      ->where("role_has_permissions.permission_id", $permissionId)
      ->pluck('role_has_permissions.role_id')
      ->toArray();
  }
}
