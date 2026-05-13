<?php
// config/komipay.php

return [
    /*
    |--------------------------------------------------------------------------
    | KomiPay Configuration
    |--------------------------------------------------------------------------
    */
    
    'base_url' => env('KOMIPAY_BASE_URL', ''),
    'token_url' => env('KOMIPAY_TOKEN_URL', ''),
    'login' => env('KOMIPAY_LOGIN', ''),
    'password' => env('KOMIPAY_PASSWORD', ''),
    'api_key' => env('KOMIPAY_API_KEY', ''),
    'keypass' => env('KOMIPAY_KEYPASS', ''),
    'timeout' => env('KOMIPAY_TIMEOUT', 120),
];