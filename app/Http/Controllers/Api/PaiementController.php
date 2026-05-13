<?php
// app/Http/Controllers/Api/PaiementController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cours;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Services\KomiPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class PaiementController extends Controller
{
    protected $komiPayService;

    public function __construct(KomiPayService $komiPayService)
    {
        $this->komiPayService = $komiPayService;
    }

    /**
     * Initier un paiement (API pour Flutter)
     * 
     * @method POST
     * @endpoint /api/paiement/initier
     */
    public function initier(Request $request)
    {
        Log::info('💳 [API] DÉBUT initier', [
            'method' => $request->method(),
            'cours_id' => $request->cours_id,
            'mode_paiement' => $request->mode_paiement,
            'user_id' => auth()->id(),
        ]);

        // 1. Validation
        $validator = Validator::make($request->all(), [
            'mode_paiement' => 'required|in:CARTE,AIRTEL_MONEY,MY_NITA,AMANATA',
            'cours_id' => 'required|exists:cours,id',
        ]);
        
        // Règles spécifiques selon le mode
        if ($request->mode_paiement === 'CARTE') {
            $validator->addRules([
                'card_holder' => 'required|string|max:255',
                'card_number' => 'required|string|min:16|max:19',
                'expiry_date' => 'required|string|size:5',
                'cvv' => 'required|string|size:3',
            ]);
        } else {
            $validator->addRules([
                'telephone' => 'required|string|min:8'
            ]);
        }

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $cours = Cours::findOrFail($request->cours_id);

        // 2. Vérifier si déjà inscrit
        $dejaInscrit = Inscription::where('apprenant_id', $user->id)
            ->where('cours_id', $cours->id)
            ->exists();

        if ($dejaInscrit) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Vous êtes déjà inscrit à ce cours'
            ], 400);
        }

        // 3. Vérifier si cours gratuit ou déjà payé
        if ($cours->est_gratuit || $cours->prix <= 0) {
            // Inscription directe
            $inscription = Inscription::create([
                'apprenant_id' => $user->id,
                'cours_id' => $cours->id,
                'statut' => 'actif',
                'progression' => 0,
                'date_debut' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Inscription réussie au cours gratuit',
                'data' => [
                    'inscription_id' => $inscription->id,
                    'cours_id' => $cours->id,
                    'cours_titre' => $cours->titre
                ]
            ]);
        }

        // 4. Vérifier si paiement déjà en cours
        $paiementExistant = Paiement::where('apprenant_id', $user->id)
            ->where('cours_id', $cours->id)
            ->whereIn('statut', ['en_attente', 'paye'])
            ->first();

        if ($paiementExistant) {
            if ($paiementExistant->statut === 'paye') {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Vous avez déjà payé ce cours'
                ], 400);
            }
            
            return response()->json([
                'status' => 'pending',
                'message' => 'Un paiement est déjà en cours',
                'transaction_id' => $paiementExistant->transaction_id
            ]);
        }

        try {
            // 5. Créer l'enregistrement de paiement
            $transactionId = $this->genererTransactionId($user->id, $cours->id);
            
            $paiement = Paiement::create([
                'apprenant_id' => $user->id,
                'cours_id' => $cours->id,
                'montant' => $cours->prix,
                'transaction_id' => $transactionId,
                'mode_paiement' => $request->mode_paiement,
                'statut' => 'en_attente',
                'tentatives' => 1
            ]);

            // 6. Traiter selon le mode de paiement
            $result = $this->traiterPaiement($request, $cours, $user, $paiement);

            return $this->gererResultatAPI($result, $paiement, $cours);

        } catch (Exception $e) {
            Log::error('Erreur initiation paiement API', [
                'user_id' => $user->id,
                'cours_id' => $cours->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'failed',
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer un ID de transaction unique
     */
    private function genererTransactionId($userId, $coursId)
    {
        return 'DARASI_' . $userId . '_' . $coursId . '_' . time();
    }

    /**
     * Traiter le paiement selon la méthode
     */
    private function traiterPaiement(Request $request, Cours $cours, $user, Paiement $paiement)
    {
        $donneesPaiement = $this->preparerDonneesKomiPay($request, $cours, $user, $paiement);
        
        if ($request->mode_paiement === 'CARTE') {
            return $this->komiPayService->processCardPayment($donneesPaiement);
        } else {
            return $this->komiPayService->processMobileMoneyPayment($donneesPaiement);
        }
    }

    /**
     * Préparer les données pour KomiPay
     */
    private function preparerDonneesKomiPay(Request $request, Cours $cours, $user, Paiement $paiement)
    {
        $donneesBase = [
            'montant_a_payer' => $cours->prix,
            'reference_externe' => $paiement->transaction_id,
            'nom_prenom_payeur' => $user->prenom . ' ' . $user->nom,
            'api_key' => config('komipay.api_key'),
        ];

        $internalMethod = $request->mode_paiement;
        $komipayMethod = $this->komiPayService->mapPaymentMethodToKomipay($internalMethod);
        $donneesBase['mobile_money'] = $komipayMethod;

        switch ($internalMethod) {
            case 'CARTE':
                $cardNumber = preg_replace('/[^0-9]/', '', $request->card_number);
                $formatted = '';
                for ($i = 0; $i < 16; $i++) {
                    if ($i > 0 && $i % 4 === 0) $formatted .= '-';
                    $formatted .= $cardNumber[$i];
                }
                $donneesBase['numero_carte_bancaire'] = $formatted;
                $donneesBase['date_expiration'] = $request->expiry_date;
                $donneesBase['cvv_number'] = $request->cvv;
                $donneesBase['javaEnabled'] = false;
                $donneesBase['javascriptEnabled'] = true;
                $donneesBase['screenHeight'] = '1080';
                $donneesBase['screenWidth'] = '1920';
                $donneesBase['TZ'] = '1';
                $donneesBase['challengeWindowSize'] = '05';
                break;

            case 'AIRTEL_MONEY':
            case 'MY_NITA':
            case 'AMANATA':
                $donneesBase['numero_telephone_payeur'] = $this->formatPhoneNumber($request->telephone);
                break;
        }

        return $donneesBase;
    }

    /**
     * Formater le numéro de téléphone
     */
    private function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/\s+/', '', $phone);
        if (!str_starts_with($phone, '+227')) {
            $phone = '+227' . $phone;
        }
        return $phone;
    }

    /**
     * Gérer le résultat du paiement (API)
     */
    private function gererResultatAPI($result, Paiement $paiement, Cours $cours)
    {
        switch ($result['status']) {
            case 'success':
                return $this->finaliserPaiementReussi($paiement, $cours);

            case 'pending':
                // Mettre à jour la référence KomiPay si disponible
                if (isset($result['reference_komipay'])) {
                    $paiement->update(['reference_komipay' => $result['reference_komipay']]);
                }

                // Si redirection 3DS, retourner l'URL
                if (isset($result['redirect_url'])) {
                    return response()->json([
                        'status' => 'redirect',
                        'message' => 'Redirection 3DS nécessaire',
                        'redirect_url' => $result['redirect_url'],
                        'transaction_id' => $paiement->transaction_id
                    ]);
                }

                // Paiement en attente (mobile money)
                return response()->json([
                    'status' => 'pending',
                    'message' => $result['message'] ?? 'Paiement en attente de confirmation',
                    'transaction_id' => $paiement->transaction_id,
                    'reference_komipay' => $paiement->reference_komipay
                ]);

            case 'failed':
                $paiement->update([
                    'statut' => 'echoue',
                    'erreur_message' => $result['message'] ?? 'Paiement échoué'
                ]);

                return response()->json([
                    'status' => 'failed',
                    'message' => $result['message'] ?? 'Échec du paiement'
                ], 400);

            default:
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Erreur inconnue'
                ], 500);
        }
    }

    /**
     * Finaliser un paiement réussi
     */
    private function finaliserPaiementReussi(Paiement $paiement, Cours $cours)
    {
        return DB::transaction(function () use ($paiement, $cours) {
            $paiement->update([
                'statut' => 'paye',
                'date_paiement' => now()
            ]);

            $inscription = Inscription::firstOrCreate(
                [
                    'apprenant_id' => $paiement->apprenant_id,
                    'cours_id' => $paiement->cours_id
                ],
                [
                    'statut' => 'actif',
                    'progression' => 0,
                    'date_debut' => now()
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Paiement réussi !',
                'data' => [
                    'inscription_id' => $inscription->id,
                    'cours_id' => $cours->id,
                    'cours_titre' => $cours->titre,
                    'transaction_id' => $paiement->transaction_id
                ]
            ]);
        });
    }

    /**
     * Vérifier le statut d'un paiement
     * 
     * @method GET
     * @endpoint /api/paiement/statut/{transaction_id}
     */
    public function statut($transactionId)
    {
        $user = auth()->user();
        
        $paiement = Paiement::where('transaction_id', $transactionId)
            ->where('apprenant_id', $user->id)
            ->first();

        if (!$paiement) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Paiement non trouvé'
            ], 404);
        }

        if ($paiement->statut === 'paye') {
            return response()->json([
                'status' => 'success',
                'paiement_status' => 'paye',
                'message' => 'Paiement déjà confirmé'
            ]);
        }

        if (!$paiement->reference_komipay) {
            return response()->json([
                'status' => 'pending',
                'paiement_status' => $paiement->statut,
                'message' => 'En attente de confirmation'
            ]);
        }

        try {
            $komipayStatus = $this->komiPayService->checkTransactionStatus($paiement->reference_komipay);
            
            if ($komipayStatus === 'success') {
                $result = $this->finaliserPaiementReussi($paiement, $paiement->cours);
                return $result;
            }
            
            if ($komipayStatus === 'failed') {
                $paiement->update(['statut' => 'echoue']);
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Le paiement a échoué'
                ]);
            }
            
            $paiement->increment('tentatives');
            
            return response()->json([
                'status' => 'pending',
                'message' => 'Paiement en cours de traitement'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'unknown',
                'message' => 'Erreur de vérification'
            ], 500);
        }
    }

    /**
     * Webhook KomiPay
     * 
     * @method POST
     * @endpoint /api/webhooks/komipay
     */
    public function webhook(Request $request)
    {
        Log::info('Webhook KomiPay reçu', $request->all());

        $reference = $request->get('reference_transaction') ?? $request->get('reference');
        
        if (!$reference) {
            return response()->json(['error' => 'Référence manquante'], 400);
        }

        $paiement = Paiement::where('reference_komipay', $reference)->first();
        
        if (!$paiement) {
            Log::warning('Paiement non trouvé pour webhook', ['reference' => $reference]);
            return response()->json(['error' => 'Paiement non trouvé'], 404);
        }

        $statut = $request->get('statut') ?? $request->get('etat');

        if ($statut === 'SUCCESS' || $statut === 'success') {
            $this->finaliserPaiementReussi($paiement, $paiement->cours);
        } elseif ($statut === 'FAILED' || $statut === 'failed') {
            $paiement->update(['statut' => 'echoue']);
        }

        return response()->json(['status' => 'ok']);
    }
}