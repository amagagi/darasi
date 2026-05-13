<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
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
        $mapping = [
            'CARTE' => 'bank_card',
            'CREDIT_CARD' => 'bank_card',
            'AIRTEL_MONEY' => 'airtel_money',
            'MY_NITA' => 'nita_transfert',
            'AMANATA' => 'amana_transfert'
        ];

        if (!array_key_exists($internalMethod, $mapping)) {
            throw new Exception('Méthode de paiement non supportée: ' . $internalMethod);
        }

        return $mapping[$internalMethod];
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
        $phone = preg_replace('/\s+/', '', $phone);
        
        if (!str_starts_with($phone, '+227')) {
            $phone = '+227' . $phone;
        }
        
        return $phone;
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
                ->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ]
                ])
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
                    Cache::put($cacheKey, $data['token'], now()->addMinutes(45));
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
     * Crypter le CVV pour la sécurité
     */
    public function encryptCvv($cvv, $token)
    {
        try {
            $response = Http::timeout(30)
                ->withOptions(['verify' => false])
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
            $token = $this->refreshToken();
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

            Log::debug('Payload carte', $payload);

            $response = Http::timeout($this->timeout)
                ->withOptions(['verify' => false])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                    'keypass' => $this->keypass
                ])
                ->post($this->baseUrl . '/b2c_standard', $payload);

            if (!$response->successful()) {
                throw new Exception('Erreur HTTP: ' . $response->status());
            }

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
            $token = $this->refreshToken();
            if (!$token) {
                throw new Exception('Impossible d\'obtenir le token d\'authentification');
            }

            $requestData = [
                'mobile_money' => $paymentData['mobile_money'],
                'api_key' => $this->apiKey,
                'montant_a_payer' => $this->formatAmount($paymentData['montant_a_payer']),
                'numero_telephone_payeur' => $this->formatPhoneNumber($paymentData['numero_telephone_payeur']),
                'nom_prenom_payeur' => $paymentData['nom_prenom_payeur'],
                'pays_payeur' => $paymentData['pays_payeur'] ?? 'Niger',
                'reference_externe' => $paymentData['reference_externe']
            ];

            $response = Http::timeout($this->timeout)
                ->withOptions(['verify' => false])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ])
                ->post($this->baseUrl . '/b2c_standard', $requestData);

            if (!$response->successful()) {
                throw new Exception('Erreur HTTP: ' . $response->status());
            }

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
            'reference_komipay' => $referenceKomipay
        ];
    }

    /**
     * Déterminer le statut à partir de la réponse
     */
    private function determinerStatut($responseData)
    {
        if (isset($responseData['code']) && $responseData['code'] == '200') {
            $message = strtolower($responseData['message'] ?? '');
            
            $pendingKeywords = ['en attente', 'pending', 'valider', 'confirmer', 'code'];
            foreach ($pendingKeywords as $keyword) {
                if (str_contains($message, $keyword)) {
                    return 'pending';
                }
            }
            
            return 'success';
        }

        if (isset($responseData['statut'])) {
            return $responseData['statut'] === true ? 'success' : 'failed';
        }

        return 'pending';
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
                $paiement->update(['reference_komipay' => $reference]);
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

            $response = Http::timeout(30)
                ->withOptions(['verify' => false])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ])
                ->post($this->baseUrl . '/check-transaction-status', [
                    'api_key' => $this->apiKey,
                    'reference_transaction' => $referenceKomipay,
                    'login' => $this->login,
                    'keypass' => $this->keypass
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->determineStatusFromResponse($data);
            }

            return 'unknown';

        } catch (Exception $e) {
            Log::error('Erreur vérification statut', ['message' => $e->getMessage()]);
            return 'unknown';
        }
    }

    /**
     * Déterminer le statut depuis la réponse de vérification
     */
    private function determineStatusFromResponse($data)
    {
        if (isset($data['etat'])) {
            $etat = strtoupper($data['etat']);
            if (in_array($etat, ['SUCCESS', 'TERMINE', 'VALIDÉ', 'VALIDEE'])) return 'success';
            if (in_array($etat, ['ECHEC', 'FAILED', 'REFUSÉ', 'REJECTED'])) return 'failed';
            if (in_array($etat, ['ATTENTE', 'PENDING', 'EN_COURS', 'EN_ATTENTE'])) return 'pending';
        }

        if (isset($data['statut'])) {
            return $data['statut'] === true ? 'success' : 'failed';
        }

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
}