<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserActive
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // 1. Vérifier si le compte n'est pas désactivé (pour tous)
            if (!$user->is_active) {
                Auth::logout();
                return redirect()->route('login')
                    ->with('error', 'Votre compte a été désactivé. Veuillez contacter l\'administrateur.');
            }
            
            // 2. Vérifier la validation pour les formateurs uniquement
            if ($user->role === 'formateur' && !$user->email_verified_at) {
                Auth::logout();
                return redirect()->route('login')
                    ->with('error', 'Votre compte formateur est en attente de validation par l\'administrateur.');
            }
        }

        return $next($request);
    }
}
