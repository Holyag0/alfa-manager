<?php
namespace App\Http\Middleware;

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
                'profile_photo_url' => $user->profile_photo_url
            ];
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $authUser,
            ],
            'flash' => function () use ($request) {
                $flash = [
                    'success' => $request->session()->get('success'),
                    'error' => $request->session()->get('error'),
                ];
                $guardian = $request->session()->get('guardian');
                if (!is_null($guardian)) {
                    $flash['guardian'] = $guardian;
                }
                $student = $request->session()->get('student');
                if (!is_null($student)) {
                    $flash['student'] = $student;
                }
                return $flash;
            },
        ]);
    }
}
