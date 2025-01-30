<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ServiceUsers;
use Inertia\Inertia;

class UserController extends Controller
{
     /**
     * @var ServiceUsers
     */
    private $users;

    public function __construct(ServiceUsers $user) {
        $this->users = $user;
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
    public function store(Request $request) {
        $data = $request;
        $this->users()->create($data);
    }
    public function show() {

    }
    public function edit(Request $request) {

    }
    public function destroy(Request $request) {

    }
}   