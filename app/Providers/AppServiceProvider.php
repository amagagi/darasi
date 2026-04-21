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
    }

    public function register(): void
    {
        //
    }
}