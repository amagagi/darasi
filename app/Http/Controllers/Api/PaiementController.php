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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class PaiementController extends Controller
{
    /**
     * Au-delà de ce délai, une tentative restée « en_attente » est abandonnée
     * pour ne plus bloquer un nouvel essai.
     *
     * Calé sur la fenêtre réelle de KomiPay, mesurée en conditions réelles :
     * « Timeout transaction 5 minutes depassé ». On ajoute une marge pour le
     * temps de propagation, mais bloquer l'apprenant plus longtemps n'aurait
     * aucun fondement — la transaction est déjà morte côté fournisseur.
     */
    private const MINUTES_EXPIRATION_TENTATIVE = 10;

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
            // AIRTEL_MONEY retire : reponse nulle de KomiPay (cf. KomiPayService).
            'mode_paiement' => 'required|in:CARTE,MY_NITA,AMANATA',
            'cours_id' => 'required|exists:cours,id',
        ]);
        
        // Règles spécifiques selon le mode
        if ($request->mode_paiement === 'CARTE') {
            $validator->addRules([
                'card_holder' => 'required|string|max:255',
                // `min:16` comptait les CARACTÈRES : « 1234 5678 9012 345 »
                // (15 chiffres) passait la validation, puis faisait planter le
                // formatage. On valide donc les chiffres, pas la saisie brute.
                'card_number' => ['required', 'string', $this->regleNumeroCarte()],
                // MM/AA, mois valide et carte non expirée.
                'expiry_date' => ['required', 'string', 'size:5', $this->regleExpiration()],
                'cvv' => 'required|string|digits_between:3,4',
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
        //
        // Un paiement resté « en_attente » ne bloquait auparavant l'utilisateur
        // SANS AUCUNE LIMITE DE TEMPS. Combiné à komipay:sync qui ignore les
        // paiements sans reference_komipay, un premier échec interdisait
        // définitivement l'achat du cours. On abandonne donc les tentatives
        // trop anciennes avant de tester.
        Paiement::where('apprenant_id', $user->id)
            ->where('cours_id', $cours->id)
            ->where('statut', 'en_attente')
            ->where('created_at', '<', now()->subMinutes(self::MINUTES_EXPIRATION_TENTATIVE))
            ->update(['statut' => 'echoue']);

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
     * Numéro de carte : 13 à 19 chiffres, validé par l'algorithme de Luhn.
     *
     * Le contrôle de Luhn détecte les fautes de frappe localement, ce qui évite
     * un aller-retour inutile vers la banque et un message d'échec obscur pour
     * l'apprenant.
     */
    private function regleNumeroCarte(): \Closure
    {
        return function (string $attribut, $valeur, \Closure $echec) {
            $chiffres = preg_replace('/\D/', '', (string) $valeur);
            $longueur = strlen($chiffres);

            if ($longueur < 13 || $longueur > 19) {
                $echec('Le numéro de carte doit comporter entre 13 et 19 chiffres.');
                return;
            }

            // Luhn : on double un chiffre sur deux en partant de la droite.
            $somme = 0;
            $doubler = false;
            for ($i = $longueur - 1; $i >= 0; $i--) {
                $n = (int) $chiffres[$i];
                if ($doubler) {
                    $n *= 2;
                    if ($n > 9) {
                        $n -= 9;
                    }
                }
                $somme += $n;
                $doubler = ! $doubler;
            }

            if ($somme % 10 !== 0) {
                $echec('Le numéro de carte est invalide.');
            }
        };
    }

    /** Date d'expiration au format MM/AA, mois valide et carte non expirée. */
    private function regleExpiration(): \Closure
    {
        return function (string $attribut, $valeur, \Closure $echec) {
            if (! preg_match('#^(0[1-9]|1[0-2])/(\d{2})$#', (string) $valeur, $m)) {
                $echec('La date d\'expiration doit être au format MM/AA.');
                return;
            }

            // Dernier jour du mois : une carte reste valable jusqu'à sa fin.
            $fin = \Carbon\Carbon::createFromDate(2000 + (int) $m[2], (int) $m[1], 1)->endOfMonth();

            if ($fin->isPast()) {
                $echec('Cette carte est expirée.');
            }
        };
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
                // Groupement par 4 séparés par des tirets, quelle que soit la
                // longueur. L'ancienne boucle lisait 16 caractères en dur, sans
                // contrôle de borne : une carte de moins de 16 chiffres
                // provoquait une erreur fatale PHP (« Uninitialized string
                // offset »), une carte de 19 chiffres était tronquée en
                // silence — donc refusée par la banque.
                $chiffres = preg_replace('/\D/', '', (string) $request->card_number);
                $donneesBase['numero_carte_bancaire'] = implode('-', str_split($chiffres, 4));
                $donneesBase['date_expiration'] = $request->expiry_date;
                $donneesBase['cvv_number'] = $request->cvv;
                $donneesBase['javaEnabled'] = false;
                $donneesBase['javascriptEnabled'] = true;
                $donneesBase['screenHeight'] = '1080';
                $donneesBase['screenWidth'] = '1920';
                $donneesBase['TZ'] = '1';
                $donneesBase['challengeWindowSize'] = '05';
                break;

            // case 'AIRTEL_MONEY': retiré — cf. KomiPayService.
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
    /**
     * Délègue au service : cette logique existait en trois copies divergentes
     * (ici, dans ApprenantAbonnementController et dans KomiPayService).
     */
    private function formatPhoneNumber($phone)
    {
        return $this->komiPayService->formatPhoneNumber($phone);
    }

    /**
     * Gérer le résultat du paiement (API)
     */
    private function gererResultatAPI($result, Paiement $paiement, Cours $cours)
    {
        switch ($result['status']) {
            case 'success':
                $this->komiPayService->finaliserPaiement($paiement);

                $inscription = Inscription::where('apprenant_id', $paiement->apprenant_id)
                    ->where('cours_id', $cours->id)
                    ->first();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Paiement réussi !',
                    'data' => [
                        'inscription_id' => $inscription?->id,
                        'cours_id' => $cours->id,
                        'cours_titre' => $cours->titre,
                        'transaction_id' => $paiement->transaction_id
                    ]
                ]);

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
                    'reference_komipay' => $paiement->reference_komipay,
                    // MyNITA : le message invite à régler « en guichet NITA via
                    // le code d'achat ». Sans ce champ, l'apprenant n'a aucun
                    // moyen de l'obtenir.
                    'code_achat' => $result['code_achat'] ?? $paiement->code_achat,
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
                'message' => 'En attente de confirmation',
                'code_achat' => $paiement->code_achat,
            ]);
        }

        try {
            $komipayStatus = $this->komiPayService->checkTransactionStatus($paiement->reference_komipay);
            
            if ($komipayStatus === 'success') {
                $this->komiPayService->finaliserPaiement($paiement);

                if ($paiement->cours_id) {
                    $inscription = Inscription::where('apprenant_id', $paiement->apprenant_id)
                        ->where('cours_id', $paiement->cours_id)
                        ->first();

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Paiement réussi !',
                        'data' => [
                            'inscription_id' => $inscription?->id,
                            'cours_id' => $paiement->cours_id,
                            'cours_titre' => $paiement->cours?->titre,
                            'transaction_id' => $paiement->transaction_id
                        ]
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'paiement_status' => 'paye',
                    'message' => 'Paiement confirmé'
                ]);
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
                'message' => 'Paiement en cours de traitement',
                // Renvoye a chaque sondage : l'apprenant garde son code
                // d'achat MyNITA meme s'il recharge l'ecran.
                'code_achat' => $paiement->code_achat,
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
        $reference = $request->get('reference_transaction') ?? $request->get('reference');

        Log::info('Webhook KomiPay reçu', ['reference' => $reference]);

        if (!$reference) {
            return response()->json(['error' => 'Référence manquante'], 400);
        }

        $paiement = Paiement::where('reference_komipay', $reference)->first();

        if (!$paiement) {
            Log::warning('Paiement non trouvé pour webhook', ['reference' => $reference]);
            return response()->json(['error' => 'Paiement non trouvé'], 404);
        }

        if ($paiement->statut === 'paye') {
            // Déjà finalisé (webhook rejoué par KomiPay) : rien à refaire.
            return response()->json(['status' => 'ok']);
        }

        // Le corps du webhook n'est jamais authentifié (KomiPay ne fournit pas
        // de signature à vérifier ici) : on ne s'en sert que comme
        // déclencheur, jamais comme source de vérité pour le statut. Sans ce
        // rappel serveur-à-serveur, n'importe quel utilisateur connaissant sa
        // propre reference_komipay (renvoyée par l'API lors de l'initiation
        // du paiement) pourrait se déclarer "payé" en falsifiant ce webhook.
        $statutReel = $this->komiPayService->checkTransactionStatus($paiement->reference_komipay);

        if ($statutReel === 'success') {
            $this->komiPayService->finaliserPaiement($paiement);
        } elseif ($statutReel === 'failed') {
            $paiement->update(['statut' => 'echoue']);
        }

        return response()->json(['status' => 'ok']);
    }
}