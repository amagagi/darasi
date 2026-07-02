<?php
// app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;  // ← AJOUTER

class AuthController extends Controller
{
    /**
     * Inscription d'un nouvel utilisateur
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|string|email|max:150|unique:users',
            'telephone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'sometimes|in:apprenant,formateur',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'password' => $request->password,  // ← Hash::make() retiré (le mutateur s'en occupe)
            'role' => $request->role ?? 'apprenant',
            'is_active' => ($request->role === 'formateur') ? false : true,  // ← Formateur en attente
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inscription réussie',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Connexion d'un utilisateur
     * Accepte email OU numéro de téléphone
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 🔥 Rate Limiting : Limiter les tentatives
        $login = $request->login;
        $key = 'login-attempt:' . md5($login);  // Clé unique par login

        // Vérifier si trop de tentatives
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);
            
            return response()->json([
                'error' => 'Trop de tentatives de connexion.',
                'message' => "Veuillez réessayer dans {$minutes} minute(s).",
                'retry_after' => $seconds
            ], 429);
        }

        // Déterminer si c'est un email ou un téléphone
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
        } else {
            $field = 'telephone';
        }

        $user = User::where($field, $login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // 🔥 Incrémenter les tentatives échouées
            RateLimiter::hit($key, 60); // Bloquer pendant 60 secondes
            
            throw ValidationException::withMessages([
                'login' => ['Les identifiants sont incorrects.'],
            ]);
        }

        // ✅ Vérifier si le compte est actif
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'login' => ['Votre compte est en attente de validation. Veuillez contacter l\'administrateur.'],
            ]);
        }

        // ✅ Vérifier si le formateur est validé par l'admin
        if ($user->role === 'formateur' && !$user->email_verified_at) {
            throw ValidationException::withMessages([
                'login' => ['Votre compte formateur est en attente de validation par l\'administrateur.'],
            ]);
        }

        // 🔥 Réinitialiser les tentatives (connexion réussie)
        RateLimiter::clear($key);

        // Supprimer les anciens tokens
        $user->tokens()->delete();

        // Créer un nouveau token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie',
        ]);
    }

    /**
     * Récupérer l'utilisateur connecté
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Mettre à jour le profil
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|string|max:100',
            'prenom' => 'sometimes|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'avatar' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update($request->only(['nom', 'prenom', 'telephone', 'avatar']));

        return response()->json([
            'message' => 'Profil mis à jour',
            'user' => $user,
        ]);
    }

    /**
     * Envoyer un lien de réinitialisation du mot de passe
     * 
     * @method POST
     * @endpoint /api/forgot-password
     */
    public function forgotPassword(Request $request)
    {
        try {
            \Log::info('### DEBUT forgotPassword ###');
            \Log::info('Email: ' . $request->email);
            
            $request->validate(['email' => 'required|email|exists:users,email']);
            
            \Log::info('Validation OK');
            
            $broker = Password::broker();
            \Log::info('Broker OK');
            
            $status = $broker->sendResetLink(
                $request->only('email')
            );
            
            \Log::info('Status: ' . $status);
            
            return response()->json(['success' => true, 'message' => 'Email envoyé']);
            
        } catch (\Exception $e) {
            \Log::error('EXCEPTION: ' . $e->getMessage());
            \Log::error('LIGNE: ' . $e->getLine());
            \Log::error('FICHIER: ' . $e->getFile());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Réinitialiser le mot de passe avec le token
     * 
     * @method POST
     * @endpoint /api/reset-password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6|confirmed',
        ]);
        
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );
        
        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Mot de passe réinitialisé avec succès.'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Token invalide ou email incorrect.'
        ], 400);
    }

    /**
     * Changer le mot de passe (utilisateur connecté)
     * 
     * @method POST
     * @endpoint /api/change-password
     * @requires Auth
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);
        
        $user = $request->user();
        
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Le mot de passe actuel est incorrect.'
            ], 400);
        }
        
        $user->forceFill([
            'password' => Hash::make($request->password)
        ])->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Mot de passe modifié avec succès.'
        ]);
    }

    /**
     * Vérifier l'email de l'utilisateur
     * 
     * @method GET
     * @endpoint /api/email/verify/{id}/{hash}
     */
    public function verifyEmail($id, $hash)
    {
        $user = User::findOrFail($id);
        
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Lien de vérification invalide.'], 400);
        }
        
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email déjà vérifié.'], 400);
        }
        
        $user->markEmailAsVerified();
        
        return response()->json([
            'success' => true,
            'message' => 'Email vérifié avec succès.'
        ]);
    }

    /**
     * Renvoyer l'email de vérification
     * 
     * @method POST
     * @endpoint /api/email/resend
     * @requires Auth
     */
    public function resendVerification(Request $request)
    {
        try {
            $user = $request->user();
            \Log::info('resendVerification - Utilisateur: ' . $user->email);
            
            if ($user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email déjà vérifié.'], 400);
            }
            
            $user->sendEmailVerificationNotification();
            \Log::info('resendVerification - Notification envoyée');
            
            return response()->json([
                'success' => true,
                'message' => 'Email de vérification renvoyé.'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('resendVerification ERROR: ' . $e->getMessage());
            \Log::error('Ligne: ' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}