<?php
// app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * CONTROLLER D'AUTHENTIFICATION
 * 
 * @description Gère l'inscription, la connexion, la déconnexion et le profil utilisateur
 * @author amagagi
 * @version 1.0
 */
class AuthController extends Controller
{
    /**
     * Inscription d'un nouvel utilisateur
     * 
     * @method POST
     * @endpoint /api/register
     * 
     * @body_param string nom required - Nom de l'utilisateur
     * @body_param string prenom required - Prénom de l'utilisateur
     * @body_param string email required - Email unique
     * @body_param string telephone optional - Numéro de téléphone
     * @body_param string password required - Mot de passe (min 6 caractères)
     * @body_param string password_confirmation required - Confirmation du mot de passe
     * 
     * @response 201 {
     *   "message": "Inscription réussie",
     *   "user": {...},
     *   "token": "1|xxxxx"
     * }
     * 
     * @response 422 {
     *   "errors": {...}
     * }
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|string|email|max:150|unique:users',
            'telephone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'password' => Hash::make($request->password),
            'role' => 'apprenant',
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
     * 
     * @method POST
     * @endpoint /api/login
     * 
     * @body_param string email required - Email de l'utilisateur
     * @body_param string password required - Mot de passe
     * 
     * @response 200 {
     *   "message": "Connexion réussie",
     *   "user": {...},
     *   "token": "1|xxxxx"
     * }
     * 
     * @response 422 {
     *   "errors": {"email": ["Les identifiants sont incorrects."]}
     * }
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        // Supprime les anciens tokens
        $user->tokens()->delete();
        
        // Crée un nouveau token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Déconnexion
     * 
     * @method POST
     * @endpoint /api/logout
     * @requires Auth (Bearer Token)
     * 
     * @response 200 {
     *   "message": "Déconnexion réussie"
     * }
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie']);
    }

    /**
     * Récupérer le profil de l'utilisateur connecté
     * 
     * @method GET
     * @endpoint /api/me
     * @requires Auth (Bearer Token)
     * 
     * @response 200 {
     *   "id": 3,
     *   "nom": "Apprenant",
     *   "prenom": "Test",
     *   "email": "apprenant@darasi.com",
     *   "role": "apprenant"
     * }
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Mettre à jour le profil
     * 
     * @method PUT
     * @endpoint /api/profile
     * @requires Auth (Bearer Token)
     * 
     * @body_param string nom optional - Nouveau nom
     * @body_param string prenom optional - Nouveau prénom
     * @body_param string telephone optional - Nouveau téléphone
     * 
     * @response 200 {
     *   "message": "Profil mis à jour",
     *   "user": {...}
     * }
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|string|max:100',
            'prenom' => 'sometimes|string|max:100',
            'telephone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update($request->only(['nom', 'prenom', 'telephone']));

        return response()->json([
            'message' => 'Profil mis à jour',
            'user' => $user,
        ]);
    }
}