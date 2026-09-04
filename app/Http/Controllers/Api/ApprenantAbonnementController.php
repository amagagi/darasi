<?php
// app/Http/Controllers/Api/ApprenantAbonnementController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbonnementType;
use App\Models\AbonnementSouscrit;
use App\Models\Categorie;
use App\Models\Paiement;
use App\Models\Cours;
use App\Models\Inscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * CONTROLLER ABONNEMENTS APPRENANT
 * 
 * @description Gère la souscription aux abonnements par catégorie
 * @author amagagi
 * @version 1.0
 */
class ApprenantAbonnementController extends Controller
{
    /**
     * Liste des formules d'abonnement disponibles
     * 
     * @method GET
     * @endpoint /api/abonnements
     * @requires Auth (Bearer Token)
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "nom": "Mensuel",
     *       "categorie": {
     *         "id": 1,
     *         "nom": "Gestion de Projet"
     *       },
     *       "prix": 5000,
     *       "duree_jours": 30,
     *       "est_populaire": true
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $abonnements = AbonnementType::where('est_actif', true)
            ->with('categorie')
            ->orderBy('ordre')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $abonnements
        ]);
    }

    /**
     * Formules d'abonnement par catégorie
     * 
     * @method GET
     * @endpoint /api/abonnements/categorie/{categorie_id}
     * @requires Auth (Bearer Token)
     */
    public function parCategorie($categorieId)
    {
        $abonnements = AbonnementType::where('est_actif', true)
            ->where('categorie_id', $categorieId)
            ->with('categorie')
            ->orderBy('ordre')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $abonnements
        ]);
    }

    /**
         * Souscrire à un abonnement
         * 
         * @method POST
         * @endpoint /api/abonnements/souscrire
         * @requires Auth (Bearer Token)
         * 
         * @body_param int abonnement_type_id required - ID de la formule
         * @body_param string mode_paiement required - CARTE, MY_NITA, AMANATA
         * @body_param string telephone required for mobile money - Numéro de téléphone
         * 
         * @response 200 {
         *   "status": "pending",
         *   "message": "Abonnement initié",
         *   "abonnement_id": 1,
         *   "transaction_id": "ABO_3_4_xxxxx"
         * }
         */
        public function souscrire(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'abonnement_type_id' => 'required|exists:abonnements_types,id',
                // AIRTEL_MONEY retire : reponse nulle de KomiPay (cf. KomiPayService).
                'mode_paiement' => 'required|in:CARTE,MY_NITA,AMANATA',
                'telephone' => 'required_if:mode_paiement,MY_NITA,AMANATA|string|min:8'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $formule = AbonnementType::with('categorie')->findOrFail($request->abonnement_type_id);

            // Vérifier si déjà un abonnement actif pour cette catégorie
            $abonnementExistant = AbonnementSouscrit::where('apprenant_id', $user->id)
                ->where('categorie_id', $formule->categorie_id)
                ->where('statut', 'actif')
                ->exists();

            if ($abonnementExistant) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Vous avez déjà un abonnement actif pour cette catégorie'
                ], 400);
            }

            try {
                DB::beginTransaction();

                // Créer l'abonnement en attente
                $abonnement = AbonnementSouscrit::create([
                    'apprenant_id' => $user->id,
                    'type_abonnement_id' => $formule->id,
                    'categorie_id' => $formule->categorie_id,
                    'date_debut' => now(),
                    'date_fin' => now()->addDays($formule->duree_jours),
                    'statut' => 'en_attente'
                ]);

                // Créer le paiement associé
                $transactionId = 'ABO_' . $user->id . '_' . $formule->id . '_' . time();

                $paiement = Paiement::create([
                    'apprenant_id' => $user->id,
                    'abonnement_type_id' => $formule->id,
                    'montant' => $formule->prix,
                    'transaction_id' => $transactionId,
                    'mode_paiement' => $request->mode_paiement,
                    'statut' => 'en_attente'
                ]);

                // Lier le paiement à l'abonnement
                $abonnement->update(['paiement_id' => $paiement->id]);

                DB::commit();

                // ==============================================
                // INTÉGRATION KOMIPAY POUR LE PAIEMENT
                // ==============================================
                
                $komiPayService = app(\App\Services\KomiPayService::class);
                
                // Préparer les données pour KomiPay
                $komipayMethod = $komiPayService->mapPaymentMethodToKomipay($request->mode_paiement);
                
                $paymentData = [
                    'mobile_money' => $komipayMethod,
                    'montant_a_payer' => $formule->prix,
                    'reference_externe' => $transactionId,
                    'nom_prenom_payeur' => $user->prenom . ' ' . $user->nom,
                    'api_key' => config('komipay.api_key'),
                ];
                
                // Ajouter le téléphone pour les paiements mobile
                if ($request->mode_paiement !== 'CARTE') {
                    $paymentData['numero_telephone_payeur'] = $this->formatPhoneNumber($request->telephone);
                }
                
                // Appeler KomiPay
                if ($request->mode_paiement === 'CARTE') {
                    // Pour carte bancaire
                    $validator->addRules([
                        'card_holder' => 'required|string|max:255',
                        'card_number' => 'required|string|min:16|max:19',
                        'expiry_date' => 'required|string|size:5',
                        'cvv' => 'required|string|size:3',
                    ]);
                    
                    $paymentData['numero_carte_bancaire'] = $request->card_number;
                    $paymentData['date_expiration'] = $request->expiry_date;
                    $paymentData['cvv_number'] = $request->cvv;
                    
                    $result = $komiPayService->processCardPayment($paymentData);
                } else {
                    // Mobile money
                    $result = $komiPayService->processMobileMoneyPayment($paymentData);
                }
                
                // Mettre à jour la référence KomiPay
                if (isset($result['reference_komipay'])) {
                    $paiement->update(['reference_komipay' => $result['reference_komipay']]);
                }
                
                // Gérer le résultat
                if ($result['status'] === 'success') {
                    // Paiement immédiatement réussi
                    $abonnement->update(['statut' => 'actif']);
                    $paiement->update(['statut' => 'paye', 'date_paiement' => now()]);
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Abonnement activé avec succès',
                        'abonnement_id' => $abonnement->id,
                        'transaction_id' => $transactionId
                    ]);
                }
                
                if ($result['status'] === 'pending') {
                    return response()->json([
                        'status' => 'pending',
                        'message' => 'Abonnement initié. En attente de confirmation du paiement.',
                        'abonnement_id' => $abonnement->id,
                        'transaction_id' => $transactionId,
                        'reference_komipay' => $paiement->reference_komipay
                    ]);
                }
                
                // Échec du paiement
                $abonnement->update(['statut' => 'annule']);
                $paiement->update(['statut' => 'echoue']);
                
                return response()->json([
                    'status' => 'failed',
                    'message' => $result['message'] ?? 'Échec du paiement'
                ], 400);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur souscription abonnement', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'status' => 'failed',
                    'message' => 'Erreur: ' . $e->getMessage()
                ], 500);
            }
        }

        /**
         * Formater le numéro de téléphone
         */
        private function formatPhoneNumber($phone)
        {
            // Délègue au service : cette logique existait en trois copies,
            // dont deux transformaient « 22790000159 » en « +22722790000159 ».
            return app(\App\Services\KomiPayService::class)->formatPhoneNumber($phone);
        }
    /**
     * Mes abonnements actifs
     * 
     * @method GET
     * @endpoint /api/mes-abonnements
     * @requires Auth (Bearer Token)
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "categorie": {...},
     *       "type": {...},
     *       "date_fin": "2025-12-25",
     *       "jours_restants": 30,
     *       "statut": "actif"
     *     }
     *   ]
     * }
     */
    public function mesAbonnements(Request $request)
    {
        $user = $request->user();

        $abonnements = AbonnementSouscrit::where('apprenant_id', $user->id)
            ->with(['type', 'categorie'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($abo) {
                return [
                    'id' => $abo->id,
                    'categorie' => $abo->categorie,
                    'type' => $abo->type,
                    'date_debut' => $abo->date_debut,
                    'date_fin' => $abo->date_fin,
                    'jours_restants' => $abo->isActif() ? $abo->joursRestants() : 0,
                    'est_actif' => $abo->isActif(),
                    'expire_bientot' => $abo->expireBientot(),
                    'statut' => $abo->statut
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $abonnements
        ]);
    }


    /**
     * Vérifier le statut d'un paiement d'abonnement
     * 
     * @method GET
     * @endpoint /api/abonnements/statut/{transaction_id}
     */
    public function statutPaiement($transactionId)
    {
        $user = auth()->user();
        
        $paiement = Paiement::where('transaction_id', $transactionId)
            ->where('apprenant_id', $user->id)
            ->first();
        
        if (!$paiement) {
            return response()->json(['status' => 'failed', 'message' => 'Paiement non trouvé'], 404);
        }
        
        if ($paiement->statut === 'paye') {
            return response()->json(['status' => 'success', 'message' => 'Paiement confirmé']);
        }
        
        // Vérifier auprès de KomiPay
        $komiPayService = app(\App\Services\KomiPayService::class);
        $komipayStatus = $komiPayService->checkTransactionStatus($paiement->reference_komipay);
        
        if ($komipayStatus === 'success') {
            // Mettre à jour l'abonnement
            $abonnement = AbonnementSouscrit::where('paiement_id', $paiement->id)->first();
            if ($abonnement) {
                $abonnement->update(['statut' => 'actif']);
            }
            $paiement->update(['statut' => 'paye', 'date_paiement' => now()]);
            
            return response()->json(['status' => 'success', 'message' => 'Paiement confirmé']);
        }
        
        return response()->json(['status' => 'pending', 'message' => 'En attente de confirmation']);
    }

    /**
     * Vérifier si l'utilisateur a accès à un cours via abonnement
     * 
     * @method GET
     * @endpoint /api/abonnements/verifier/{cours_id}
     * @requires Auth (Bearer Token)
     */
    public function verifierAcces($coursId)
    {
        $user = auth()->user();
        
        // Correction : utiliser le modèle Cours avec le bon namespace
        $cours = \App\Models\Cours::findOrFail($coursId);
        
        $aAcces = $user->peutAccederCours($cours);
        
        $abonnementsActifs = $user->abonnements()
            ->where('statut', 'actif')
            ->where('date_fin', '>', now())
            ->with('categorie')
            ->get();
        
        return response()->json([
            'success' => true,
            'a_acces' => $aAcces,
            'cours_id' => $coursId,
            'cours_titre' => $cours->titre,
            'abonnements_actifs' => $abonnementsActifs
        ]);
    }

    /**
     * Annuler un abonnement
     * 
     * @method POST
     * @endpoint /api/abonnements/annuler/{id}
     * @requires Auth (Bearer Token)
     */
    public function annuler($id)
    {
        $user = auth()->user();

        $abonnement = AbonnementSouscrit::where('id', $id)
            ->where('apprenant_id', $user->id)
            ->firstOrFail();

        if ($abonnement->statut !== 'actif') {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les abonnements actifs peuvent être annulés'
            ], 400);
        }

        $abonnement->update(['statut' => 'annule']);

        return response()->json([
            'success' => true,
            'message' => 'Abonnement annulé avec succès'
        ]);
    }
}