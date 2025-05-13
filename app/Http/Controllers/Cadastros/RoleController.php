<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Services\ServiceRole;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    /**
     * @var ServiceRole
     */
    private $role;

    /**
     * Constructor with dependency injection
     *
     * @param ServiceRole $role
     */
    public function __construct(ServiceRole $role)
    {
        // $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index']]);
        // $this->middleware('permission:role-create', ['only' => ['create', 'store']]);
        // $this->middleware('permission:role-edit', ['only' => ['edit', 'update']]);
        // $this->middleware('permission:role-delete', ['only' => ['destroy']]);
        $this->role = $role;
    }

    //  public static function middleware(): array
    // {
    //     return [
    //         // Aplicar middleware de permissão para métodos específicos
    //         new Middleware('permission:role-create', only: ['create','store']),
    //         new Middleware('permission:role-read', only: ['index','show']),
    //         new Middleware('permission:role-edit', only: ['edit','update']),
    //         new Middleware('permission:role-delete', only: ['destroy']),
    //     ];
    // }

    /**
     * Display a listing of the roles
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $roles = $this->role->all();

        return Inertia::render('Cadastros/Roles/index', [
            'roles' => $roles
        ]);
    }

    /**
     * Show the form for creating a new role
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $permissions = $this->role->getAllPermissions();

        return Inertia::render('Cadastros/Roles/create', [
            'permissions' => $permissions
        ]);
    }

    /**
     * Store a newly created role
     *
     * @param  RoleRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->role->store($request->all());

        return redirect()->route('roles.index')
            ->with('success', 'Papel cadastrado com sucesso.');
    }

    /**
     * Display the specified role
     *
     * @param  int  $id
     * @return \Inertia\Response
     */
    public function show($id)
    {
        $role = $this->role->show($id);

        return Inertia::render('Cadastros/Roles/show', [
            'role' => $role
        ]);
    }

    /**
     * Show the form for editing the specified role
     *
     * @param  int  $id
     * @return \Inertia\Response
     */
    public function edit($id)
    {
        $role = $this->role->show($id);
        $permissions = $this->role->getAllPermissions();
        $rolePermissions = $this->role->getRolePermissions($id);

        return Inertia::render('Cadastros/Roles/edit', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions
        ]);
    }

    /**
     * Update the specified role
     *
     * @param  RoleRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $this->role->update($id, $request->all());

        return redirect()->route('roles.index')
            ->with('success', 'Papel atualizado com sucesso.');
    }

    /**
     * Remove the specified role
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $this->role->destroy($id);

        return redirect()->route('roles.index')
            ->with('success', 'Papel removido com sucesso.');
    }
}