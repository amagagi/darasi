<?php
// app/Http/Controllers/Api/ModuleController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cours;
use App\Models\Module;
use Illuminate\Http\Request;

/**
 * CONTROLLER DES MODULES
 * 
 * @description Gère les modules (chapitres) d'un cours
 * @author amagagi
 * @version 1.0
 */
class ModuleController extends Controller
{
    /**
     * Liste des modules d'un cours
     * 
     * @method GET
     * @endpoint /api/cours/{cours_id}/modules
     * @requires Auth (Bearer Token) - L'utilisateur doit être inscrit au cours
     * 
     * @url_param int cours_id required - ID du cours
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "titre": "Introduction",
     *       "description": "...",
     *       "ordre": 1,
     *       "duree_estimee": 30,
     *       "lecons": [...]
     *     }
     *   ]
     * }
     * 
     * @response 403 {
     *   "error": "Vous devez être inscrit à ce cours"
     * }
     */
    public function index($cours_id)
    {
        $user = auth()->user();
        
        // Vérifier si l'utilisateur est inscrit au cours
        $cours = Cours::findOrFail($cours_id);
        
        if (!$cours->est_gratuit) {
            $estInscrit = $user && $user->inscriptions()->where('cours_id', $cours_id)->exists();
            if (!$estInscrit) {
                return response()->json([
                    'error' => 'Vous devez être inscrit à ce cours pour accéder aux modules.'
                ], 403);
            }
        }
        
        $modules = Module::where('cours_id', $cours_id)
            ->with(['lecons' => function($query) {
                $query->orderBy('ordre');
            }])
            ->orderBy('ordre')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $modules
        ]);
    }
    
    /**
     * Détail d'un module avec ses leçons
     * 
     * @method GET
     * @endpoint /api/modules/{id}
     * @requires Auth (Bearer Token)
     * 
     * @url_param int id required - ID du module
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "titre": "Introduction",
     *     "lecons": [...]
     *   }
     * }
     */
    public function show($id)
    {
        $module = Module::with(['lecons' => function($query) {
                $query->orderBy('ordre');
            }])
            ->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $module
        ]);
    }
}