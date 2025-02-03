<?php

namespace App\Http\Controllers\Cadastros;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UsersRequest;
use App\Services\ServiceUsers;
use Inertia\Inertia;

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
            new Middleware('permission:show-users', only: ['index']),
            new Middleware('permission:create-users', only: ['create', 'store']),
            new Middleware('permission:edit-users', only: ['edit', 'update']),
            new Middleware('permission:delete-users', only: ['destroy']),
        ];
    }

   
     /**
     * @return ServiceUsers
     */
    public function users()
    {
        return $this->users;
    }

    public function index() {
       return Inertia::render('Users/Index');
    }
    public function create() {

    }
    public function store(UsersRequest $request) {
        $data = $request;
        dd($data);
        $this->users()->create($data);
    }
    public function show() {

    }
    public function edit(Request $request) {

    }
    public function destroy(Request $request) {

    }
}   