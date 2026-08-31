<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ReCaptchaService;

class VerifyRecaptcha
{
    protected ReCaptchaService $recaptcha;

    public function __construct(ReCaptchaService $recaptcha)
    {
        $this->recaptcha = $recaptcha;
    }

    public function handle(Request $request, Closure $next)
    {
        // 🔥 MODE SKIP : Si reCAPTCHA n'est pas configuré, OU si on est en local (mode développement)
        if (empty(config('services.recaptcha.secret')) || app()->environment('local')) {
            return $next($request);
        }

        // Si c'est une requête API (Sanctum)
        if ($request->is('api/*')) {
            $token = $request->input('recaptcha_token');
            
            if (!$token) {
                return response()->json([
                    'error' => 'Le token reCAPTCHA est requis',
                    'message' => 'Veuillez compléter la vérification de sécurité'
                ], 422);
            }

            $isValid = $this->recaptcha->isValid($token, $request->ip());

            if (!$isValid) {
                return response()->json([
                    'error' => 'Vérification de sécurité échouée',
                    'message' => 'Veuillez réessayer'
                ], 422);
            }

            return $next($request);
        }

        // Si c'est une requête web (Filament ou autre)
        $token = $request->input('g-recaptcha-response');
        
        if (!$token) {
            return back()->withErrors([
                'recaptcha' => 'Veuillez compléter la vérification de sécurité'
            ]);
        }

        $isValid = $this->recaptcha->isValid($token, $request->ip());

        if (!$isValid) {
            return back()->withErrors([
                'recaptcha' => 'Vérification de sécurité échouée, veuillez réessayer'
            ]);
        }

        return $next($request);
    }
}