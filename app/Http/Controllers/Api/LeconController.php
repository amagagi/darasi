<?php
// app/Http/Controllers/Api/LeconController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cours;
use App\Models\Lecon;
use App\Models\Inscription;
use Illuminate\Http\Request;

/**
 * CONTROLLER DES LEÇONS
 * 
 * @description Gère les leçons (vidéos, PDF) des modules
 * @author amagagi
 * @version 1.0
 */
class LeconController extends Controller
{
    /**
     * Afficher le contenu d'une leçon
     * 
     * @method GET
     * @endpoint /api/lecons/{id}/contenu
     * @requires Auth (Bearer Token) - L'utilisateur doit être inscrit au cours
     * 
     * @url_param int id required - ID de la leçon
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "titre": "Introduction",
     *     "type_contenu": "video",
     *     "url": "https://...",
     *     "is_completed": false
     *   }
     * }
     * 
     * @response 404 {
     *   "error": "Leçon non trouvée"
     * }
     * 
     * @response 403 {
     *   "error": "Vous devez être inscrit à ce cours"
     * }
     */
    public function contenu($id)
    {
        $user = auth()->user();
        
        $lecon = Lecon::with('module.cours')->findOrFail($id);
        
        // Vérifier si l'utilisateur est inscrit au cours
        $cours = $lecon->module->cours;
        
        if (!$cours->est_gratuit) {
            $estInscrit = $user && $user->inscriptions()->where('cours_id', $cours->id)->exists();
            if (!$estInscrit) {
                return response()->json([
                    'error' => 'Vous devez être inscrit à ce cours pour accéder à cette leçon.'
                ], 403);
            }
        }
        
        // Vérifier si la leçon est déjà complétée par l'utilisateur
        $isCompleted = false;
        if ($user) {
            $inscription = Inscription::where('apprenant_id', $user->id)
                ->where('cours_id', $cours->id)
                ->first();
            
            if ($inscription) {
                $progres = $inscription->progresLecons()
                    ->where('lecon_id', $id)
                    ->first();
                $isCompleted = $progres && $progres->est_complete;
            }
        }
        
        // Construire la réponse selon le type de contenu
        $response = [
            'success' => true,
            'data' => [
                'id' => $lecon->id,
                'titre' => $lecon->titre,
                'type_contenu' => $lecon->type_contenu,
                'is_completed' => $isCompleted
            ]
        ];
        
        if ($lecon->type_contenu === 'video') {
            $response['data']['url'] = $lecon->url_video;
            $response['data']['duree'] = $lecon->duree_video;
        } elseif ($lecon->type_contenu === 'pdf') {
            $response['data']['url'] = asset('storage/' . $lecon->url_pdf);
        } elseif ($lecon->type_contenu === 'article') {
            $response['data']['contenu'] = $lecon->contenu_text;
        }
        
        return response()->json($response);
    }
    
    /**
     * Marquer une leçon comme complétée
     * 
     * @method POST
     * @endpoint /api/lecons/{id}/complete
     * @requires Auth (Bearer Token)
     * 
     * @url_param int id required - ID de la leçon
     * @body_param int temps_passe optional - Temps passé en secondes
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Leçon marquée comme complétée",
     *   "progression": 45.5
     * }
     */
    public function marquerComplete(Request $request, $id)
    {
        $user = auth()->user();
        $lecon = Lecon::with('module.cours')->findOrFail($id);
        $cours = $lecon->module->cours;
        
        // Vérifier l'inscription
        $inscription = Inscription::where('apprenant_id', $user->id)
            ->where('cours_id', $cours->id)
            ->first();
        
        if (!$inscription) {
            return response()->json([
                'error' => 'Vous n\'êtes pas inscrit à ce cours.'
            ], 403);
        }
        
        // Marquer la leçon comme complétée
        $progres = $inscription->progresLecons()->updateOrCreate(
            ['lecon_id' => $id],
            [
                'est_complete' => true,
                'temps_passe' => $request->temps_passe ?? 0,
                'date_completion' => now()
            ]
        );
        
        // Calculer la progression globale du cours
        $totalLecons = Lecon::whereHas('module', function($q) use ($cours) {
            $q->where('cours_id', $cours->id);
        })->count();
        
        $completedLecons = $inscription->progresLecons()
            ->where('est_complete', true)
            ->count();
        
        $progression = $totalLecons > 0 ? ($completedLecons / $totalLecons) * 100 : 0;
        
        // Mettre à jour la progression dans l'inscription
        $inscription->update(['progression' => $progression]);
        
        return response()->json([
            'success' => true,
            'message' => 'Leçon marquée comme complétée',
            'progression' => round($progression, 2)
        ]);
    }
}