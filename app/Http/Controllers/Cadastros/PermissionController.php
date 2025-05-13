<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use App\Services\ServicePermission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PermissionController extends Controller
{
    /**
     * @var ServicePermission
     */
    private $permission;

    /**
     * Constructor with dependency injection
     *
     * @param ServicePermission $permission
     */
    public function __construct(ServicePermission $permission)
    {
        // $this->middleware('permission:permission-list|permission-create|permission-edit|permission-delete', ['only' => ['index']]);
        // $this->middleware('permission:permission-create', ['only' => ['create', 'store']]);
        // $this->middleware('permission:permission-edit', ['only' => ['edit', 'update']]);
        // $this->middleware('permission:permission-delete', ['only' => ['destroy']]);
        $this->permission = $permission;
    }

    /**
     * Display a listing of the permissions
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $permissions = $this->permission->all();

        return Inertia::render('Cadastros/Permissions/index', [
            'permissions' => $permissions
        ]);
    }

    /**
     * Show the form for creating a new permission
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Cadastros/Permissions/create');
    }

    /**
     * Store a newly created permission
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:permissions,name',
        ]);

        $this->permission->store($request->all());

        return redirect()->route('permissions.index')
            ->with('success', 'Permissão cadastrada com sucesso.');
    }

    /**
     * Display the specified permission
     *
     * @param  int  $id
     * @return \Inertia\Response
     */
    public function show($id)
    {
        $permission = $this->permission->show($id);

        return Inertia::render('Cadastros/Permissions/show', [
            'permission' => $permission
        ]);
    }

    /**
     * Show the form for editing the specified permission
     *
     * @param  int  $id
     * @return \Inertia\Response
     */
    public function edit($id)
    {
        $permission = $this->permission->show($id);

        return Inertia::render('Cadastros/Permissions/edit', [
            'permission' => $permission
        ]);
    }

    /**
     * Update the specified permission
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|unique:permissions,name,' . $id,
        ]);

        $this->permission->update($id, $request->all());

        return redirect()->route('permissions.index')
            ->with('success', 'Permissão atualizada com sucesso.');
    }

    /**
     * Remove the specified permission
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $this->permission->destroy($id);

        return redirect()->route('permissions.index')
            ->with('success', 'Permissão removida com sucesso.');
    }
}