<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\AbonnementSouscrit;
use App\Models\Inscription;
use App\Models\Paiement;
use Exception;

class KomiPayService
{
    private $baseUrl;
    private $tokenUrl;
    private $login;
    private $password;
    private $apiKey;
    private $keypass;
    private $timeout;

    public function __construct()
    {
        $this->baseUrl = config('komipay.base_url');
        $this->tokenUrl = config('komipay.token_url');
        $this->login = config('komipay.login');
        $this->password = config('komipay.password');
        $this->apiKey = config('komipay.api_key');
        $this->keypass = config('komipay.keypass');
        $this->timeout = config('komipay.timeout', 120);
        
        Log::debug('KomiPayService initialisé', [
            'base_url' => $this->baseUrl,
            'login' => $this->login,
        ]);
    }

    /**
     * Mapper les méthodes de paiement internes vers KomiPay
     */
    public function mapPaymentMethodToKomipay($internalMethod)
    {
        // AIRTEL_MONEY retiré le 04/09/2026.
        // Constaté en production : `airtel_money` renvoie HTTP 200 avec un
        // corps entièrement nul — {"code":null,"statut":null,"message":null} —
        // sans reference_transaction, et ce quel que soit le numéro. Les autres
        // méthodes non autorisées (moov_flooz, zamani_cash) répondent, elles,
        // un 400 explicite : il s'agit donc d'une anomalie propre à cette
        // méthode chez KomiPay, pas d'un refus normal.
        // Conséquence : paiement sans référence, donc impossible à réconcilier.
        // Pour réactiver : remettre la ligne ci-dessous, l'option dans les
        // règles de validation des contrôleurs, et le choix dans les deux
        // écrans de paiement Flutter.
        //   'AIRTEL_MONEY' => 'airtel_money',
        $mapping = [
            'CARTE' => 'bank_card',
            'CREDIT_CARD' => 'bank_card',
            'MY_NITA' => 'nita_transfert',
            'AMANATA' => 'amana_transfert'
        ];

        if (!array_key_exists($internalMethod, $mapping)) {
            throw new Exception('Méthode de paiement non supportée: ' . $internalMethod);
        }

        return $mapping[$internalMethod];
    }

    /**
     * Méthodes relevant des sociétés de transfert d'argent (STA), dont le
     * format de requête diffère de celui des opérateurs téléphoniques.
     */
    private function estTransfertSTA(string $mobileMoney): bool
    {
        return in_array($mobileMoney, [
            'nita_transfert',
            'amana_transfert',
            'zeyna_transfert',
        ], true);
    }

    /**
     * Formater le montant pour KomiPay
     */
    public function formatAmount($amount)
    {
        return (string) intval($amount);
    }

    /**
     * Formater le numéro de téléphone
     */
    public function formatPhoneNumber($phone)
    {
        // Ne garder que les chiffres et un éventuel « + » de tête.
        $numero = preg_replace('/[^\d+]/', '', (string) $phone);
        $numero = ltrim($numero, '+');

        // Préfixe international composé (00227…) puis indicatif nu (227…).
        // L'ancienne version ajoutait « +227 » dès que la chaîne ne commençait
        // pas par « +227 », transformant « 22790000159 » en
        // « +22722790000159 » — numéro invalide, paiement rejeté.
        if (str_starts_with($numero, '00227')) {
            $numero = substr($numero, 5);
        } elseif (str_starts_with($numero, '227') && strlen($numero) > 8) {
            $numero = substr($numero, 3);
        }

        return '+227' . $numero;
    }

    /**
     * Rafraîchir le token (forcer un nouveau)
     */
    public function refreshToken()
    {
        Cache::forget('komipay_token');
        Log::info('Token KomiPay forcément rafraîchi');
        return $this->getToken();
    }

    /**
     * Obtenir le token d'authentification KomiPay
     */
    public function getToken()
    {
        $cacheKey = 'komipay_token';
        
        $cachedToken = Cache::get($cacheKey);
        if ($cachedToken) {
            return $cachedToken;
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->tokenUrl, [
                    'login' => $this->login,
                    'password' => $this->password,
                    'api_key' => $this->apiKey
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['token'])) {
                    // La documentation annonce une validité d'environ 30 min.
                    // Le cache était réglé sur 45 : entre la 30e et la 45e
                    // minute, on servait un jeton déjà expiré. 25 min laisse
                    // une marge de sécurité.
                    Cache::put($cacheKey, $data['token'], now()->addMinutes(25));
                    return $data['token'];
                }
            }

            Log::error('Erreur génération token KomiPay', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;

        } catch (Exception $e) {
            Log::error('Exception génération token KomiPay', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Appel authentifié à KomiPay, avec réessai unique si le jeton est refusé.
     *
     * Les méthodes de paiement forçaient auparavant un `refreshToken()` à
     * chaque appel, ce qui annulait le cache de 45 minutes : toute transaction
     * provoquait une authentification complète (latence, et risque de butter
     * sur une limitation de débit du fournisseur). On réutilise donc le jeton
     * en cache, et on ne le renouvelle que s'il est effectivement rejeté.
     */
    private function appelAuthentifie(string $endpoint, array $payload, array $entetesSupp = [])
    {
        $envoyer = function (string $token) use ($endpoint, $payload, $entetesSupp) {
            return Http::timeout($this->timeout)
                ->withHeaders(array_merge([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ], $entetesSupp))
                ->post($this->baseUrl . $endpoint, $payload);
        };

        $token = $this->getToken();
        if (! $token) {
            throw new Exception('Impossible d\'obtenir le token d\'authentification');
        }

        $response = $envoyer($token);

        // Jeton expiré côté KomiPay avant la fin de notre fenêtre de cache.
        if ($response->status() === 401 || $response->status() === 403) {
            Log::info('Jeton KomiPay rejeté, renouvellement puis réessai', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
            ]);

            $token = $this->refreshToken();
            if (! $token) {
                throw new Exception('Impossible d\'obtenir le token d\'authentification');
            }

            $response = $envoyer($token);
        }

        if (! $response->successful()) {
            throw new Exception('Erreur HTTP: ' . $response->status());
        }

        return $response;
    }

    /**
     * Crypter le CVV pour la sécurité
     */
    public function encryptCvv($cvv, $token)
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                    'keypass' => $this->keypass
                ])
                ->post($this->baseUrl . '/crypt-cvv', [
                    'api_key' => $this->apiKey,
                    'cvv_number' => $cvv
                ]);

            if (!$response->successful()) {
                throw new Exception('Erreur HTTP cryptage CVV: ' . $response->status());
            }

            $data = $response->json();
            
            if (!isset($data['cvv_encrpyt'])) {
                throw new Exception('Réponse cryptage CVV invalide');
            }

            return $data['cvv_encrpyt'];

        } catch (Exception $e) {
            Log::error('Erreur cryptage CVV', ['message' => $e->getMessage()]);
            throw new Exception('Impossible de crypter le CVV: ' . $e->getMessage());
        }
    }

    /**
     * Traiter un paiement par carte bancaire
     */
        public function processCardPayment($paymentData)
    {
        try {
            // Jeton en cache : le chiffrement du CVV en a besoin explicitement.
            // Un éventuel rejet est traité plus bas par appelAuthentifie().
            $token = $this->getToken();
            if (!$token) {
                throw new Exception('Impossible d\'obtenir le token d\'authentification');
            }

            $cvvEncrypted = $this->encryptCvv($paymentData['cvv_number'], $token);

            // Construire le payload SANS numero_telephone_payeur
            $payload = [
                'mobile_money' => 'bank_card',
                'api_key' => $this->apiKey,
                'nom_prenom_payeur' => $paymentData['nom_prenom_payeur'],
                'numero_carte_bancaire' => $paymentData['numero_carte_bancaire'],
                'date_expiration' => $paymentData['date_expiration'],
                'cvv_number' => $cvvEncrypted,
                'montant_a_payer' => $this->formatAmount($paymentData['montant_a_payer']),
                'reference_externe' => $paymentData['reference_externe'],
                'javaEnabled' => $paymentData['javaEnabled'] ?? false,
                'javascriptEnabled' => $paymentData['javascriptEnabled'] ?? true,
                'screenHeight' => '1080',
                'screenWidth' => '1920',
                'TZ' => '1',
                'challengeWindowSize' => '05'
            ];

            // Ne jamais logger $payload complet : il contient le numéro de
            // carte, le nom du porteur et la date d'expiration en clair
            // (seul le CVV est chiffré à ce stade) — violation PCI-DSS.
            Log::debug('Paiement carte initié', [
                'reference_externe' => $payload['reference_externe'],
                'montant_a_payer' => $payload['montant_a_payer'],
            ]);

            $response = $this->appelAuthentifie('/b2c_standard', $payload, [
                'keypass' => $this->keypass,
            ]);

            return $this->handleCardPaymentResponse($response->json(), $paymentData['reference_externe']);

        } catch (Exception $e) {
            Log::error('Erreur paiement carte', ['message' => $e->getMessage()]);
            throw $e;
        }
    }
    /**
     * Traiter la réponse pour paiement carte
     */
    private function handleCardPaymentResponse($responseData, $transactionId)
    {
        if (isset($responseData['statut']) && $responseData['statut'] === false) {
            throw new Exception($responseData['message'] ?? 'Erreur de paiement inconnue');
        }

        if (isset($responseData['etat'])) {
            switch ($responseData['etat']) {
                case 'SUCCESS':
                    return [
                        'status' => 'success',
                        'message' => 'Paiement traité avec succès',
                        'transaction_id' => $transactionId,
                        'reference_komipay' => $responseData['dataTransaction']['reference_transaction'] ?? null
                    ];

                case 'ATTENTE':
                    return [
                        'status' => 'pending',
                        'message' => 'Redirection nécessaire pour authentification 3DS',
                        'transaction_id' => $transactionId,
                        'redirect_url' => $responseData['redirect_portail_auth'] ?? null,
                        'reference_komipay' => $responseData['dataTransaction']['reference_transaction'] ?? null
                    ];

                default:
                    throw new Exception('État de transaction non géré: ' . $responseData['etat']);
            }
        }

        throw new Exception('Réponse invalide du service de paiement');
    }

    /**
     * Traiter un paiement mobile money
     */
    public function processMobileMoneyPayment($paymentData)
    {
        try {
            // Le jeton est obtenu (et renouvelé si rejeté) par appelAuthentifie().
            // La documentation définit deux formats distincts. Nous envoyions
            // auparavant un mélange des deux (tous les champs à la fois).
            $requestData = [
                'mobile_money' => $paymentData['mobile_money'],
                'api_key' => $this->apiKey,
                'montant_a_payer' => $this->formatAmount($paymentData['montant_a_payer']),
                'numero_telephone_payeur' => $this->formatPhoneNumber($paymentData['numero_telephone_payeur']),
                'reference_externe' => $paymentData['reference_externe'],
            ];

            if ($this->estTransfertSTA($paymentData['mobile_money'])) {
                // Sociétés de transfert (myNITA, Amanata) : pays du payeur.
                $requestData['pays_payeur'] = $paymentData['pays_payeur'] ?? 'Niger';
            } else {
                // Opérateurs téléphoniques (Airtel...) : nom du payeur.
                $requestData['nom_prenom_payeur'] = $paymentData['nom_prenom_payeur'];
            }

            $response = $this->appelAuthentifie('/b2c_standard', $requestData);

            $responseData = $response->json();
            
            return $this->handleMobileMoneyResponse($responseData, $paymentData);

        } catch (Exception $e) {
            Log::error('Erreur paiement mobile', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Traiter la réponse pour paiement mobile money
     */
    private function handleMobileMoneyResponse($responseData, $paymentData)
    {
        if (isset($responseData['statut']) && $responseData['statut'] === false) {
            throw new Exception($responseData['message'] ?? 'Erreur de paiement mobile');
        }

        $referenceKomipay = $responseData['reference_transaction'] ?? $responseData['reference'] ?? null;

        // Stocker la référence KomiPay
        $this->stockerReferenceKomipay($responseData, $paymentData['reference_externe'], $paymentData['mobile_money']);

        // Analyser le statut
        $statut = $this->determinerStatut($responseData);

        return [
            'status' => $statut,
            'message' => $responseData['message'] ?? 'Paiement mobile initié',
            'transaction_id' => $paymentData['reference_externe'],
            'reference_komipay' => $referenceKomipay,
            // MyNITA uniquement : permet le règlement dans un guichet NITA,
            // seule option pour un apprenant qui n'a pas l'application.
            'code_achat' => $responseData['code_achat'] ?? null,
        ];
    }

    /**
     * Déterminer le statut à partir de la réponse
     */
    /**
     * Statut d'une réponse d'INITIATION mobile money.
     *
     * Ne renvoie JAMAIS 'success'. Vérifié en conditions réelles : KomiPay
     * répond `{"code":200,"statut":true,...}` avec le message « Votre paiement
     * est en attente de validation […] Vous avez 5 minutes ». Un `statut: true`
     * signifie donc « demande acceptée », jamais « argent encaissé ».
     *
     * L'ancienne version déduisait l'état de mots-clés présents dans le
     * message : une simple reformulation par KomiPay l'aurait fait basculer sur
     * 'success', donnant accès au cours sans paiement. Seul
     * check-transaction-status fait foi pour confirmer.
     */
    private function determinerStatut($responseData)
    {
        $accepte = ($responseData['code'] ?? null) == 200
            || ($responseData['statut'] ?? null) === true;

        if ($accepte) {
            return 'pending';
        }

        // Refus immédiat (solde insuffisant, numéro inconnu, méthode
        // indisponible...) : inutile de faire patienter l'utilisateur.
        return 'failed';
    }

    /**
     * Stocker la référence KomiPay
     */
    private function stockerReferenceKomipay($responseData, $transactionId, $method)
    {
        $reference = $responseData['reference_transaction'] 
            ?? $responseData['reference'] 
            ?? null;

        if (!$reference) {
            return;
        }

        try {
            $paiement = Paiement::where('transaction_id', $transactionId)->first();
            if ($paiement) {
                $paiement->update([
                    'reference_komipay' => $reference,
                    // Conservé pour le support : un apprenant qui rappelle
                    // peut se voir redonner son code.
                    'code_achat' => $responseData['code_achat'] ?? null,
                ]);
                Log::info('Référence KomiPay stockée', [
                    'paiement_id' => $paiement->id,
                    'reference_komipay' => $reference
                ]);
            }
        } catch (Exception $e) {
            Log::error('Erreur stockage référence', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Vérifier le statut d'une transaction
     */
    public function checkTransactionStatus($referenceKomipay)
    {
        try {
            $token = $this->getToken();
            if (!$token) {
                return 'unknown';
            }

            // Timeout court et ASSUMÉ : mesuré en conditions réelles, cet
            // endpoint ne répond pas tant que la transaction STA est en
            // attente (4 appels expirés à 45 s, puis réponse immédiate dès
            // l'expiration de la fenêtre de 5 min). Un timeout signifie donc
            // « toujours en attente », pas une panne — inutile d'immobiliser
            // un worker PHP 45 secondes pour l'apprendre.
            $response = Http::timeout(12)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ])
                // ATTENTION : cet endpoint attend « apikey » SANS underscore,
                // contrairement à tous les autres qui utilisent « api_key »
                // (cf. documentation KomiPay, check-transaction-status).
                // Nous envoyions « api_key » : la vérification échouait donc
                // systématiquement et renvoyait 'unknown', si bien qu'AUCUN
                // paiement n'était jamais confirmé. `login` et `keypass` ne
                // font pas partie du contrat de cet endpoint.
                ->post($this->baseUrl . '/check-transaction-status', [
                    'apikey' => $this->apiKey,
                    'reference_transaction' => $referenceKomipay,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->determineStatusFromResponse($data);
            }

            return 'unknown';

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Cas nominal pendant la fenêtre de validation : KomiPay laisse la
            // connexion ouverte. On le journalise en info, pas en erreur, pour
            // ne pas noyer les vraies pannes.
            Log::info('Vérification statut sans réponse (transaction probablement en attente)', [
                'message' => $e->getMessage(),
            ]);
            return 'unknown';
        } catch (Exception $e) {
            Log::error('Erreur vérification statut', ['message' => $e->getMessage()]);
            return 'unknown';
        }
    }

    /**
     * Déterminer le statut depuis la réponse de vérification
     */
    /**
     * Interprète la réponse de check-transaction-status.
     *
     * Principe directeur : ne conclure que sur un signal EXPLICITE. Tout le
     * reste vaut 'unknown', ce qui laisse le paiement en attente et sera
     * réessayé par komipay:sync — un faux « en attente » se rattrape, un faux
     * « échoué » fait perdre un paiement réel au client.
     *
     * L'ancienne version renvoyait 'failed' dès que `statut` valait false. Or
     * une transaction simplement pas encore enregistrée répond
     * `{"code":404,"statut":false,...,"transactionStatus":"notFound"}` : les
     * paiements légitimes étaient donc marqués échoués prématurément.
     */
    private function determineStatusFromResponse($data)
    {
        $succes = ['SUCCESS', 'SUCCES', 'TERMINE', 'TERMINEE', 'VALIDÉ', 'VALIDE', 'VALIDEE', 'PAID', 'COMPLETED'];
        $echecs = ['ECHEC', 'FAILED', 'REFUSÉ', 'REFUSE', 'REJECTED', 'CANCELLED', 'ANNULE', 'EXPIRED'];
        $attentes = ['ATTENTE', 'PENDING', 'EN_COURS', 'EN_ATTENTE', 'PROCESSING', 'INITIATED'];

        // Champ réellement renvoyé par l'API en pratique, imbriqué dans `data`
        // et absent de la documentation — constaté sur une réponse réelle.
        $candidats = array_filter([
            $data['data']['transactionStatus'] ?? null,
            $data['transactionStatus'] ?? null,
            $data['etat'] ?? null,
        ], fn ($v) => is_string($v) && $v !== '');

        foreach ($candidats as $valeur) {
            $normalise = strtoupper(trim($valeur));

            if (in_array($normalise, $succes, true)) return 'success';
            if (in_array($normalise, $echecs, true)) return 'failed';
            if (in_array($normalise, $attentes, true)) return 'pending';

            // Transaction inconnue de KomiPay : souvent trop tôt après
            // l'initiation. Surtout pas un échec.
            if ($normalise === 'NOTFOUND') return 'unknown';
        }

        // `statut === true` reste un succès explicite ; `false` ne dit rien de
        // l'état de la transaction, seulement que l'appel n'a rien confirmé.
        if (($data['statut'] ?? null) === true) {
            return 'success';
        }

        Log::info('Statut KomiPay non interprété', [
            'reponse' => array_intersect_key($data, array_flip(['code', 'statut', 'message', 'etat'])),
        ]);

        return 'unknown';
    }

    /**
     * Tester la configuration
     */
    public function debugConfig()
    {
        return [
            'tokenUrl' => $this->tokenUrl,
            'baseUrl' => $this->baseUrl,
            'login' => $this->login,
            'password_length' => $this->password ? strlen($this->password) : 0,
            'api_key_length' => $this->apiKey ? strlen($this->apiKey) : 0,
            'keypass_length' => $this->keypass ? strlen($this->keypass) : 0,
            'timeout' => $this->timeout
        ];
    }

    /**
     * Invalider le token en cache
     */
    public function clearTokenCache()
    {
        Cache::forget('komipay_token');
        Log::info('Cache token KomiPay vidé');
    }

    /**
     * Finaliser un paiement confirmé : marque comme payé, puis inscrit au
     * cours ou active l'abonnement selon ce qui est renseigné sur le
     * paiement (les deux ne sont jamais présents en même temps). Idempotent :
     * un paiement déjà "paye" n'est pas retraité. Point d'entrée unique
     * partagé par le webhook, les endpoints de vérification de statut et la
     * commande de resynchronisation — pour ne jamais avoir à corriger cette
     * logique à plusieurs endroits en même temps.
     */
    public function finaliserPaiement(Paiement $paiement): void
    {
        DB::transaction(function () use ($paiement) {
            // Relecture verrouillée : le webhook, le sondage de statut et la
            // commande de synchronisation peuvent arriver simultanément. Tester
            // le modèle en mémoire laisserait passer deux exécutions
            // concurrentes, donc une double inscription.
            $verrouille = Paiement::where('id', $paiement->id)
                ->lockForUpdate()
                ->first();

            if ($verrouille === null || $verrouille->statut === 'paye') {
                return;
            }

            $verrouille->update([
                'statut' => 'paye',
                'date_paiement' => now()
            ]);

            // Aligne l'instance appelante sur l'état réel.
            $paiement->setRawAttributes($verrouille->getAttributes(), true);

            if ($paiement->cours_id) {
                Inscription::firstOrCreate(
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
            }

            if ($paiement->abonnement_type_id) {
                AbonnementSouscrit::where('paiement_id', $paiement->id)
                    ->update(['statut' => 'actif']);
            }
        });
    }
}