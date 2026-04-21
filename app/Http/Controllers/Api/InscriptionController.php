<?php
// app/Http/Controllers/Api/InscriptionController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cours;
use App\Models\Inscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * CONTROLLER DES INSCRIPTIONS AUX COURS
 * 
 * @description Gère l'inscription des apprenants aux cours
 * @author amagagi
 * @version 1.0
 * 
 * FLUX D'INSCRIPTION :
 * 1. L'utilisateur voit les cours dans la vitrine (accès public)
 * 2. Clique sur "S'inscrire" sur un cours
 * 3. Si non connecté → redirigé vers connexion
 * 4. Si connecté → vérifications puis inscription
 * 5. Si cours payant → paiement d'abord, puis inscription
 * 6. Si cours gratuit → inscription immédiate
 */
class InscriptionController extends Controller
{
    /**
     * S'inscrire à un cours
     * 
     * @method POST
     * @endpoint /api/inscription/{cours_id}
     * @requires Auth (Bearer Token)
     * 
     * @url_param int cours_id required - ID du cours
     * 
     * @response 201 {
     *   "success": true,
     *   "message": "Inscription réussie",
     *   "data": {
     *     "id": 1,
     *     "cours_id": 1,
     *     "statut": "actif",
     *     "progression": 0
     *   }
     * }
     * 
     * @response 401 {
     *   "error": "Non authentifié"
     * }
     * 
     * @response 403 {
     *   "error": "Déjà inscrit à ce cours"
     * }
     * 
     * @response 404 {
     *   "error": "Cours non trouvé"
     * }
     * 
     * @response 402 {
     *   "error": "Cours payant",
     *   "payment_required": true,
     *   "prix": 5000,
     *   "payment_url": "/api/paiement/initier/1"
     * }
     */
    public function store(Request $request, $cours_id)
    {
        // ÉTAPE 1 : Vérifier que l'utilisateur est connecté
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 'Non authentifié. Veuillez vous connecter.'
            ], 401);
        }

        // ÉTAPE 2 : Vérifier que l'utilisateur est un apprenant
        if ($user->role !== 'apprenant') {
            return response()->json([
                'error' => 'Seuls les apprenants peuvent s\'inscrire aux cours.'
            ], 403);
        }

        // ÉTAPE 3 : Vérifier que le cours existe et est publié
        $cours = Cours::where('id', $cours_id)
            ->where('statut', 'publie')
            ->first();

        if (!$cours) {
            return response()->json([
                'error' => 'Cours non trouvé ou non disponible.'
            ], 404);
        }

        // ÉTAPE 4 : Vérifier si l'utilisateur est déjà inscrit
        $dejaInscrit = Inscription::where('apprenant_id', $user->id)
            ->where('cours_id', $cours_id)
            ->exists();

        if ($dejaInscrit) {
            return response()->json([
                'error' => 'Vous êtes déjà inscrit à ce cours.'
            ], 403);
        }

        // ÉTAPE 5 : Vérifier si le cours est payant
        if (!$cours->est_gratuit && $cours->prix > 0) {
            return response()->json([
                'error' => 'Cours payant. Veuillez effectuer le paiement d\'abord.',
                'payment_required' => true,
                'prix' => $cours->prix,
                'cours_id' => $cours_id,
                'cours_titre' => $cours->titre,
                'payment_url' => "/api/paiement/initier/{$cours_id}"
            ], 402); // 402 Payment Required
        }

        // ÉTAPE 6 : Créer l'inscription (cours gratuit)
        try {
            $inscription = Inscription::create([
                'apprenant_id' => $user->id,
                'cours_id' => $cours_id,
                'progression' => 0,
                'statut' => 'actif',
                'date_debut' => now(),
                'est_via_abonnement' => false
            ]);

            // ÉTAPE 7 : Mettre à jour le nombre d'apprenants du cours
            $cours->increment('nb_apprenants');

            // ÉTAPE 8 : Retourner la confirmation
            return response()->json([
                'success' => true,
                'message' => 'Inscription réussie ! Vous pouvez maintenant accéder au cours.',
                'data' => [
                    'id' => $inscription->id,
                    'cours_id' => $inscription->cours_id,
                    'cours_titre' => $cours->titre,
                    'statut' => $inscription->statut,
                    'progression' => $inscription->progression,
                    'date_debut' => $inscription->date_debut
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur est survenue lors de l\'inscription.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Liste des cours auxquels l'apprenant est inscrit
     * 
     * @method GET
     * @endpoint /api/mes-inscriptions
     * @requires Auth (Bearer Token)
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "cours": {...},
     *       "progression": 45.5,
     *       "statut": "actif",
     *       "date_debut": "2026-01-01"
     *     }
     *   ]
     * }
     */
    public function mesInscriptions(Request $request)
    {
        $user = $request->user();
        
        $inscriptions = Inscription::where('apprenant_id', $user->id)
            ->with(['cours' => function($query) {
                $query->select('id', 'titre', 'image_couverture', 'prix', 'est_gratuit');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $inscriptions
        ]);
    }

    /**
     * Vérifier si l'utilisateur est inscrit à un cours
     * 
     * @method GET
     * @endpoint /api/verifier-inscription/{cours_id}
     * @requires Auth (Bearer Token)
     * 
     * @response 200 {
     *   "inscrit": true,
     *   "progression": 45.5,
     *   "statut": "actif"
     * }
     */
    public function verifierInscription(Request $request, $cours_id)
    {
        $user = $request->user();
        
        $inscription = Inscription::where('apprenant_id', $user->id)
            ->where('cours_id', $cours_id)
            ->first();

        if ($inscription) {
            return response()->json([
                'inscrit' => true,
                'progression' => $inscription->progression,
                'statut' => $inscription->statut,
                'date_debut' => $inscription->date_debut
            ]);
        }

        return response()->json([
            'inscrit' => false
        ]);
    }
}