<?php

namespace App\Http\Controllers\Cadastros;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UsersRequest;
use App\Services\ServiceUsers;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class UserController extends Controller implements HasMiddleware
{
     /**
     * @var ServiceUsers
     */
    private $users;

    public function __construct(ServiceUsers $user) {
        $this->users = $user;
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
            // Loga o erro para depuração
            Log::error('Erro ao criar usuário:', ['error' => $e->getMessage()]);
            // Retorna uma mensagem de erro amigável para o usuário
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