<?php
// app/Http/Middleware/CheckUserAuthorization.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserAuthorization
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && !Auth::user()->is_auth) {
            // Usa o guard web que tem o método logout
            Auth::guard('web')->logout();
            
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')->withErrors([
                'email' => 'Sua conta não está autorizada a acessar o sistema Alfa Baby.',
            ]);
        }

        return $next($request);
    }
}