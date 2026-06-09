<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckUserActive;  // ← AJOUTE CET IMPORT

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Fusionne les deux configurations en une seule
        $middleware->append(CheckUserActive::class);
    })
    ->withSchedule(function (Schedule $schedule) {
        // Tous les jours à 8h
        $schedule->command('abonnements:check-expiration')->dailyAt('08:00');
        
        // Alternative: toutes les minutes pour test
        // $schedule->command('abonnements:check-expiration')->everyMinute();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();