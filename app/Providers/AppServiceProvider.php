<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Solution pour l'erreur "Clé trop longue" avec MySQL
        Schema::defaultStringLength(191);

        // Désactive la vérification SSL uniquement en local (pour éviter l'erreur cURL 60 avec reCAPTCHA)
        if (app()->environment('local')) {
            \Illuminate\Support\Facades\Http::globalOptions([
                'verify' => false,
            ]);
        }
    }

    public function register(): void
    {
        //
    }
}