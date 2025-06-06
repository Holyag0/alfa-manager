<?php

namespace App\Http\Controllers\Cadastros;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UsersRequest;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use App\Services\ServiceRole;
use App\Services\ServiceUsers;
use App\Http\Requests\RemoveUserRolesRequest;
use App\Http\Requests\StoreUserRoleRequest;
class UserController extends Controller implements HasMiddleware
{
     /**
     * @var ServiceUsers
     */
    private $users;
    private $roles;
    public function __construct(ServiceUsers $user, ServiceRole $roles)  {
        $this->users = $user;
        $this->roles = $roles;
    }
    public static function middleware(): array
    {
        return [
            // Aplicar middleware de permissão para métodos específicos
            new Middleware('permission:user-create', only: ['create','store']),
            new Middleware('permission:user-read', only: ['index','show']),
            new Middleware('permission:user-update', only: ['edit','update']),
            new Middleware('permission:user-delete', only: ['destroy']),
        ];
    }
     /**
     * @return ServiceUsers
     */
    public function users()
    {
        return $this->users;
    }
    public function serviceRole(){
        return $this->roles;
    }
     public function atribuirRole($id)
    {
        $user = $this->users()->show($id);
        $roles = $this->serviceRole()->roleList();
        return Inertia::render('Users/Atribuir-Roles', [
            'user' => $user,'roles' => $roles
        ]);
    }
    public function storeRoleToUser(StoreUserRoleRequest $request)
    {
        try {
            $validated = $request->validated();

            $this->users()->atribuirRolesUsers($validated['user_id'], $validated['role']);

            return redirect()->back()->with('success', 'Cargo atribuído com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atribuir role: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Ocorreu um erro ao atribuir o cargo.');
        }
    }

    public function desatribuirRole($id)
    {
        try {
            $user = $this->users()->show($id);
            if ($user->roles->isEmpty()) {
                return redirect()->route('user.index')
                ->with('error', 'Usuário não tem Nenhum cargo, Não é possivel tirar cargo.');
            }
            $roles = $user->roles;

            return Inertia::render('Users/Desatribuir-Roles', [
                'user' => $user,
                'roles' => $roles
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar desatribuição de cargos: ' . $e->getMessage());

            return redirect()->route('user.index')
                ->with('error', 'Ocorreu um erro ao carregar os cargos do usuário, não encontrado.');
        }
    }

    public function removeRoleFromUser(RemoveUserRolesRequest $request)
    {
        try {
            $validated = $request->validated();

            $user = $this->users()->desatribuirRolesUser($validated['user_id'], $validated['roles']);

            return redirect()->route('user.index')
                ->with('success', 'Cargos removidos do Usuário: ' . $user->name . ' com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao remover cargos do usuário: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Ocorreu um erro ao tentar remover os cargos do usuário.');
        }
    }


    public function index()
    {
        $users = $this->users()->all();
        return Inertia::render('Users/Index', ['users' => $users]);
    }
    public function create() {
        
    }
    public function store(UsersRequest $request) {
        try {
            $this->users()->create($request->all());
            session()->flash('success', 'Usuário criado com sucesso');
        } catch (\Exception $e) {
            Log::error('Erro ao criar usuário:', ['error' => $e->getMessage()]);
            session()->flash('error', 'Erro ao criar o usuário. Por favor, tente novamente.');
        }
    }
    public function show($id) {
        $user = $this->users()->show($id);
    
        if (!$user) {
            return session()->flash('error','Usuário Não Existe');
        }
        $sessions = $this->users()->getSession($user->id);
    
        return Inertia::render('Users/Edit',[
            'user' => $user,
            'sessions' => $sessions
        ]);
    }
    public function update(Request $request, $id) {
        $user = $this->users()->show($id);
    
        if (!$user) {
            return session()->flash('error','Usuário Não Existe');
        }
        try {
            $this->users()->update($user, $request->all());
            return redirect()->back()->with('success', 'Usuário atualizado com sucesso.');
        } catch (\Throwable $e) {
            // opcional: logar o erro
            Log::error('Erro ao atualizar usuário', ['error' => $e->getMessage()]);
           return session()->flash('error','Erro ao atualizar usuário.');
        }
    }
    public function destroy(Request $request,$id) {
        try {
            $this->users()->delete($id);
            return redirect()->back()->with('success', 'Usuário deletado com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao deletar usuário', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['Erro ao deletar usuário: ' . $e->getMessage()]);
        }
    }
}   