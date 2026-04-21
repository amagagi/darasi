<?php
// app/Http/Controllers/Api/PoleController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pole;
use Illuminate\Http\Request;

/**
 * CONTROLLER DES PÔLES
 * 
 * @description Gère les pôles (IT, Scolaire, Etudiant)
 * @author amagagi
 * @version 1.0
 */
class PoleController extends Controller
{
    /**
     * Liste de tous les pôles actifs
     * 
     * @method GET
     * @endpoint /api/poles
     * @access Public
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "nom": "IT",
     *       "description": "Cours d'informatique...",
     *       "slug": "it"
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $poles = Pole::where('is_active', true)->orderBy('ordre')->get();
        return response()->json([
            'success' => true,
            'data' => $poles
        ]);
    }

    /**
     * Liste des cours d'un pôle spécifique
     * 
     * @method GET
     * @endpoint /api/poles/{id}/cours
     * @access Public
     * 
     * @url_param int id required - ID du pôle
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "titre": "Laravel Débutant",
     *       "prix": 5000,
     *       "formateur": {...}
     *     }
     *   ]
     * }
     */
    public function cours($id)
    {
        $pole = Pole::findOrFail($id);
        $cours = $pole->cours()->where('statut', 'publie')->with('formateur')->get();
        
        return response()->json([
            'success' => true,
            'data' => $cours
        ]);
    }
}