<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckUserActive;
use App\Http\Middleware\VerifyRecaptcha;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware globaux
        $middleware->append(CheckUserActive::class);
        
        // Middlewares nommés (alias)
        $middleware->alias([
            'recaptcha' => VerifyRecaptcha::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('abonnements:check-expiration')->dailyAt('08:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();