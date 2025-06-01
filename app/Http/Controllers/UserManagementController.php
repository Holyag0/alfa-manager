<?php
// app/Http/Controllers/UserManagementController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::select('id', 'name', 'email', 'is_auth', 'created_at')
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('Admin/UserManagement', [
            'users' => $users
        ]);
    }

    public function toggleAuth(User $user)
    {
        $user->update([
            'is_auth' => $user->is_auth ? 0 : 1
        ]);

        return back()->with('success', 
            $user->is_auth 
                ? "Usuário {$user->name} foi autorizado a acessar o sistema."
                : "Usuário {$user->name} foi desautorizado do sistema."
        );
    }
}