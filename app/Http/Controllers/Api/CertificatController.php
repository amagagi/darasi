<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificat;
use App\Models\Inscription;
use Illuminate\Http\Request;

/**
 * CONTROLLER DES CERTIFICATS
 * 
 * @description Gère la consultation, vérification et téléchargement des certificats
 * @author amagagi
 * @version 1.0
 */
class CertificatController extends Controller
{
    /**
     * Liste des certificats de l'apprenant connecté
     * 
     * @method GET
     * @endpoint /api/certificats
     * @requires Auth (Bearer Token)
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "code_verification": "CERT-ABCD12-EFGH34",
     *       "date_emission": "2026-07-07 14:00:00",
     *       "est_valide": true,
     *       "cours": {
     *         "id": 1,
     *         "titre": "Laravel Débutant"
     *       },
     *       "note": 15.5
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $user = auth()->user();
        
        $certificats = Certificat::whereHas('inscription', function($q) use ($user) {
            $q->where('apprenant_id', $user->id);
        })
        ->with(['inscription.cours', 'inscription.tentativeFinal'])
        ->orderBy('date_emission', 'desc')
        ->get()
        ->map(function($certificat) {
            return [
                'id' => $certificat->id,
                'code_verification' => $certificat->code_verification,
                'date_emission' => $certificat->date_emission->toDateTimeString(),
                'est_valide' => (bool) $certificat->est_valide,
                'cours' => [
                    'id' => $certificat->inscription->cours->id,
                    'titre' => $certificat->inscription->cours->titre
                ],
                'note' => $certificat->inscription->tentativeFinal->note ?? null
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $certificats
        ]);
    }

    /**
     * Détail d'un certificat
     * 
     * @method GET
     * @endpoint /api/certificats/{id}
     * @requires Auth (Bearer Token)
     * 
     * @url_param int id required - ID du certificat
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "code_verification": "CERT-ABCD12-EFGH34",
     *     "date_emission": "2026-07-07 14:00:00",
     *     "est_valide": true,
     *     "cours": {
     *       "id": 1,
     *       "titre": "Laravel Débutant",
     *       "description": "..."
     *     },
     *     "apprenant": {
     *       "id": 3,
     *       "nom": "Apprenant",
     *       "prenom": "Test",
     *       "email": "apprenant@darasi.com"
     *     },
     *     "note": 15.5,
     *     "date_obtention": "2026-07-07 14:00:00"
     *   }
     * }
     * 
     * @response 403 {
     *   "error": "Vous n'êtes pas autorisé à voir ce certificat"
     * }
     */
    public function show($id)
    {
        $user = auth()->user();
        
        $certificat = Certificat::with([
            'inscription.cours',
            'inscription.apprenant',
            'inscription.tentativeFinal'
        ])->findOrFail($id);
        
        // Vérifier que l'utilisateur est le propriétaire OU admin
        if ($certificat->inscription->apprenant_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'error' => 'Vous n\'êtes pas autorisé à voir ce certificat'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $certificat->id,
                'code_verification' => $certificat->code_verification,
                'date_emission' => $certificat->date_emission->toDateTimeString(),
                'est_valide' => (bool) $certificat->est_valide,
                'date_revocation' => $certificat->date_revocation ? $certificat->date_revocation->toDateTimeString() : null,
                'motif_revocation' => $certificat->motif_revocation,
                'cours' => [
                    'id' => $certificat->inscription->cours->id,
                    'titre' => $certificat->inscription->cours->titre,
                    'description' => $certificat->inscription->cours->description
                ],
                'apprenant' => [
                    'id' => $certificat->inscription->apprenant->id,
                    'nom' => $certificat->inscription->apprenant->nom,
                    'prenom' => $certificat->inscription->apprenant->prenom,
                    'email' => $certificat->inscription->apprenant->email
                ],
                'note' => $certificat->inscription->tentativeFinal->note ?? null,
                'date_obtention' => $certificat->inscription->tentativeFinal->date_obtention_certificat ? 
                    $certificat->inscription->tentativeFinal->date_obtention_certificat->toDateTimeString() : null
            ]
        ]);
    }

    /**
     * Vérifier un certificat par son code (public)
     * 
     * @method GET
     * @endpoint /api/certificats/verify/{code}
     * @access Public (pas besoin de token)
     * 
     * @url_param string code required - Code de vérification
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "valide": true,
     *     "certificat": {
     *       "code": "CERT-ABCD12-EFGH34",
     *       "date_emission": "2026-07-07 14:00:00",
     *       "apprenant": "Apprenant Test",
     *       "cours": "Laravel Débutant"
     *     }
     *   }
     * }
     * 
     * @response 404 {
     *   "success": false,
     *   "error": "Certificat non trouvé"
     * }
     */
    public function verify($code)
    {
        $certificat = Certificat::with([
            'inscription.cours',
            'inscription.apprenant'
        ])->where('code_verification', $code)->first();
        
        if (!$certificat) {
            return response()->json([
                'success' => false,
                'error' => 'Certificat non trouvé'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'valide' => (bool) $certificat->est_valide,
                'certificat' => [
                    'code' => $certificat->code_verification,
                    'date_emission' => $certificat->date_emission->toDateTimeString(),
                    'apprenant' => $certificat->inscription->apprenant->prenom . ' ' . $certificat->inscription->apprenant->nom,
                    'cours' => $certificat->inscription->cours->titre
                ]
            ]
        ]);
    }

    /**
     * Télécharger le PDF d'un certificat
     * 
     * @method GET
     * @endpoint /api/certificats/{id}/pdf
     * @requires Auth (Bearer Token)
     * 
     * @url_param int id required - ID du certificat
     * 
     * @response 200 application/pdf
     * @response 403 {
     *   "error": "Vous n'êtes pas autorisé"
     * }
     * @response 404 {
     *   "error": "Certificat non trouvé"
     * }
     */
    public function downloadPdf($id)
    {
        $user = auth()->user();
        
        $certificat = Certificat::with([
            'inscription.cours',
            'inscription.apprenant',
            'inscription.tentativeFinal'
        ])->findOrFail($id);
        
        // Vérifier l'autorisation
        if ($certificat->inscription->apprenant_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'error' => 'Vous n\'êtes pas autorisé à télécharger ce certificat'
            ], 403);
        }
        
        // TODO: Générer le PDF avec DomPDF ou autre
        // Pour l'instant, retourner les données
        
        // Si un PDF est déjà généré, retourner le lien
        if ($certificat->url_pdf) {
            return response()->json([
                'success' => true,
                'data' => [
                    'certificat_id' => $certificat->id,
                    'code' => $certificat->code_verification,
                    'url_pdf' => $certificat->url_pdf,
                    'message' => 'PDF déjà généré'
                ]
            ]);
        }
        
        // Sinon, indiquer que le PDF sera généré
        return response()->json([
            'success' => true,
            'message' => 'Le PDF sera généré prochainement',
            'data' => [
                'certificat_id' => $certificat->id,
                'code' => $certificat->code_verification,
                'url_pdf' => null
            ]
        ]);
    }

    /**
     * Révoquer un certificat (Admin uniquement)
     * 
     * @method POST
     * @endpoint /api/admin/certificats/{id}/revoke
     * @requires Auth (Bearer Token + Admin)
     * 
     * @body_param string motif required - Motif de la révocation
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Certificat révoqué avec succès"
     * }
     * 
     * @response 403 {
     *   "error": "Accès réservé aux administrateurs"
     * }
     * 
     * @response 400 {
     *   "error": "Ce certificat est déjà révoqué"
     * }
     */
    public function revoke(Request $request, $id)
    {
        $user = auth()->user();
        
        // Vérifier que l'utilisateur est admin
        if ($user->role !== 'admin') {
            return response()->json([
                'error' => 'Accès réservé aux administrateurs'
            ], 403);
        }
        
        $request->validate([
            'motif' => 'required|string|min:10'
        ]);
        
        $certificat = Certificat::findOrFail($id);
        
        if (!$certificat->est_valide) {
            return response()->json([
                'error' => 'Ce certificat est déjà révoqué'
            ], 400);
        }
        
        $certificat->update([
            'est_valide' => false,
            'date_revocation' => now(),
            'revoque_par' => $user->id,
            'motif_revocation' => $request->motif
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Certificat révoqué avec succès'
        ]);
    }
}