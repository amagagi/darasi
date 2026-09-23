<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificat;
use App\Models\Inscription;
use App\Services\CertificatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * CONTROLLER DES CERTIFICATS
 * 
 * @description Gère la consultation, vérification et téléchargement des certificats
 * @author amagagi
 * @version 1.0
 */
class CertificatController extends Controller
{
    /** Validité des liens signés du PDF. */
    public const MINUTES_VALIDITE_URL = 10;

    public function __construct(private readonly CertificatService $certificats)
    {
    }

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
                'note' => $certificat->inscription->tentativeFinal->note ?? null,
                'date_revocation' => $certificat->date_revocation?->toDateTimeString(),
                'url_verification' => $this->certificats->urlVerification($certificat),
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
                'url_verification' => $this->certificats->urlVerification($certificat),
                'signataires' => $certificat->signatures->map(fn ($signature) => [
                    'nom' => $signature->nom,
                    'fonction' => $signature->fonction,
                ])->values(),
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
                    'cours' => $certificat->inscription->cours->titre,
                    'date_revocation' => $certificat->date_revocation?->toDateTimeString(),
                    // Noms et fonctions tels qu'imprimés : un tiers peut ainsi
                    // confronter le document qu'on lui présente à ce qui a
                    // réellement été délivré.
                    'signataires' => $certificat->signatures->map(fn ($signature) => [
                        'nom' => $signature->nom,
                        'fonction' => $signature->fonction,
                    ])->values(),
                ]
            ]
        ]);
    }

    /**
     * Liens de consultation et de téléchargement du PDF d'un certificat
     *
     * Le PDF lui-même est servi par `fichier()`. Cette route renvoie deux URL
     * signées, valables MINUTES_VALIDITE_URL minutes, que le navigateur ouvre
     * directement.
     *
     * @method GET
     * @endpoint /api/certificats/{id}/pdf
     * @requires Auth (Bearer Token)
     *
     * @url_param int id required - ID du certificat
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "certificat_id": 1,
     *     "code": "CERT-ABCD12-EFGH34",
     *     "url_apercu": "https://darasihub.com/api/certificats/1/fichier/apercu?expires=...&signature=...",
     *     "url_telechargement": "https://darasihub.com/api/certificats/1/fichier/telechargement?expires=...&signature=...",
     *     "expire_dans": 600
     *   }
     * }
     * @response 403 {
     *   "error": "Ce certificat a été révoqué : il ne peut plus être téléchargé."
     * }
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
        
        // Un certificat révoqué n'a plus de valeur : le laisser télécharger
        // permettrait de le présenter comme valide. L'administration garde
        // l'accès, le PDF portant alors la mention « RÉVOQUÉ ».
        if (! $certificat->est_valide && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'error' => 'Ce certificat a été révoqué : il ne peut plus être téléchargé.',
            ], 403);
        }

        // Le navigateur ouvre ces liens lui-même, sans en-tête Authorization :
        // c'est la signature, valable quelques minutes, qui autorise l'accès.
        return response()->json([
            'success' => true,
            'data' => [
                'certificat_id' => $certificat->id,
                'code' => $certificat->code_verification,
                'url_apercu' => $this->urlFichier($certificat, 'apercu'),
                'url_telechargement' => $this->urlFichier($certificat, 'telechargement'),
                'expire_dans' => self::MINUTES_VALIDITE_URL * 60,
            ],
        ]);
    }

    /**
     * Sert le PDF du certificat. L'accès est validé par la signature de l'URL.
     *
     * @method GET
     * @endpoint /api/certificats/{certificat}/fichier/{mode}
     * @access URL signée, délivrée par GET /api/certificats/{id}/pdf
     *
     * @url_param string mode required - `apercu` (affiché) ou `telechargement`
     *
     * @response 200 application/pdf
     * @response 403 Signature absente, invalide ou expirée
     */
    public function fichier(Certificat $certificat, string $mode)
    {
        $pdf = $this->certificats->genererPdf($certificat);
        $nom = $this->certificats->nomFichier($certificat);

        $reponse = $mode === 'apercu' ? $pdf->stream($nom) : $pdf->download($nom);

        // Document nominatif : ni cache partagé, ni indexation.
        $reponse->headers->set('Cache-Control', 'private, max-age=0, no-store');
        $reponse->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $reponse;
    }

    private function urlFichier(Certificat $certificat, string $mode): string
    {
        return URL::temporarySignedRoute(
            'certificats.fichier',
            now()->addMinutes(self::MINUTES_VALIDITE_URL),
            ['certificat' => $certificat->id, 'mode' => $mode],
        );
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