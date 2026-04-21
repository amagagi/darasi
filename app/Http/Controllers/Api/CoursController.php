<?php
// app/Http/Controllers/Api/CoursController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cours;
use App\Models\Module;
use Illuminate\Http\Request;

/**
 * CONTROLLER DES COURS
 * 
 * @description Gère l'affichage des cours et leur contenu
 * @author amagagi
 * @version 1.0
 */
class CoursController extends Controller
{
    /**
     * Liste de tous les cours publiés (vitrine)
     * 
     * @method GET
     * @endpoint /api/cours
     * @access Public (pas besoin de token)
     * 
     * @query_param page int optional - Numéro de page (défaut: 1)
     * @query_param per_page int optional - Nombre par page (défaut: 20)
     * 
     * @response 200 {
     *   "current_page": 1,
     *   "data": [
     *     {
     *       "id": 1,
     *       "titre": "Laravel Débutant",
     *       "description": "...",
     *       "prix": 5000,
     *       "est_gratuit": false,
     *       "image_couverture": "...",
     *       "formateur": {...},
     *       "pole": {...}
     *     }
     *   ],
     *   "last_page": 5,
     *   "total": 100
     * }
     */
    public function index()
    {
        $cours = Cours::where('statut', 'publie')
            ->with(['pole', 'formateur'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $cours
        ]);
    }

    /**
     * Détail d'un cours spécifique
     * 
     * @method GET
     * @endpoint /api/cours/{id}
     * @access Public
     * 
     * @url_param int id required - ID du cours
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "titre": "Laravel Débutant",
     *     "description": "...",
     *     "objectifs": "...",
     *     "prerequis": "...",
     *     "prix": 5000,
     *     "formateur": {...},
     *     "modules": [
     *       {
     *         "id": 1,
     *         "titre": "Introduction",
     *         "lecons": [...]
     *       }
     *     ]
     *   }
     * }
     * 
     * @response 404 {
     *   "error": "Cours non trouvé"
     * }
     */
    public function show($id)
    {
        $cours = Cours::where('id', $id)
            ->where('statut', 'publie')
            ->with(['pole', 'formateur', 'modules.lecons'])
            ->firstOrFail();
        
        return response()->json([
            'success' => true,
            'data' => $cours
        ]);
    }

    /**
     * Contenu complet d'un cours (modules + leçons)
     * 
     * @method GET
     * @endpoint /api/cours/{id}/contenu
     * @requires Auth (Bearer Token) - Sauf pour cours gratuits
     * 
     * @url_param int id required - ID du cours
     * 
     * @headers Authorization: Bearer {token}
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "titre": "Module 1",
     *       "ordre": 1,
     *       "lecons": [
     *         {
     *           "id": 1,
     *           "titre": "Leçon 1",
     *           "type_contenu": "video",
     *           "url_video": "...",
     *           "duree_video": 600
     *         }
     *       ]
     *     }
     *   ]
     * }
     * 
     * @response 403 {
     *   "error": "Vous devez être inscrit"
     * }
     */
    public function contenu($id)
    {
        $cours = Cours::findOrFail($id);
        
        // Vérifier si l'utilisateur est inscrit (sauf si cours gratuit)
        $user = auth()->user();
        if (!$cours->est_gratuit && $user) {
            $estInscrit = $user->inscriptions()->where('cours_id', $id)->exists();
            if (!$estInscrit) {
                return response()->json(['error' => 'Vous devez être inscrit'], 403);
            }
        }
        
        $modules = Module::where('cours_id', $id)
            ->with(['lecons'])
            ->orderBy('ordre')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $modules
        ]);
    }
}