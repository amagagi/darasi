<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ReCaptchaService
{
    protected string $secretKey;

    public function __construct()
    {
        $this->secretKey = config('services.recaptcha.secret');
    }

    public function verify(string $token, ?string $ip = null): array
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $this->secretKey,
            'response' => $token,
            'remoteip' => $ip,
        ]);

        return $response->json();
    }

    public function isValid(string $token, ?string $ip = null, float $minScore = 0.5): bool
    {
        $result = $this->verify($token, $ip);

        return $result['success'] && ($result['score'] ?? 0) >= $minScore;
    }
}