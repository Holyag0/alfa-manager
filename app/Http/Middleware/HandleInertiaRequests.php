<?php
namespace App\Http\Middleware;

use App\Models\Cfc;
use Inertia\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $authUser = null;
        $permissions = [];
        $roles = [];

        if ($user) {
            // Recupera as permissões do usuário
            $permissions = $user->getAllPermissions()->pluck('name');
            // Recupera os papéis do usuário
            $roles = $user->roles->pluck('name');
            
           
                

            // Organiza as informações do usuário para ser compartilhado com o frontend
            $authUser = [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'permissions' => $permissions,
                'roles'       => $roles,
            ];
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user'             => $authUser,
            ],
            'flash' => function () use ($request) {
                return [
                    'success' => $request->session()->get('success'),
                    'error'   => $request->session()->get('error'),
                ];
            },
        ]);
    }
}
