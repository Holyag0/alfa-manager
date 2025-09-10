<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CleanWizardSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se há dados do wizard na sessão
        if (session()->has('enrollment_wizard')) {
            $wizardData = session('enrollment_wizard');
            $lastActivity = session('enrollment_wizard.last_activity');
            
            // Se passou mais de 30 minutos, limpar dados
            if ($lastActivity && now()->diffInMinutes($lastActivity) > 30) {
                session()->forget('enrollment_wizard');
            } else {
                // Atualizar timestamp de atividade
                session(['enrollment_wizard.last_activity' => now()]);
            }
        }
        
        return $next($request);
    }
}
