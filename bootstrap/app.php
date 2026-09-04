<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\CheckUserActive;
use App\Http\Middleware\TrackSiteVisit;
use App\Http\Middleware\VerifyRecaptcha;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Le conteneur n'est joignable que via le nginx de l'hôte (port publié
        // sur 127.0.0.1) : on peut faire confiance à tous les proxys en amont.
        // Sans cela, Laravel ignore X-Forwarded-Proto, génère des URLs en http://
        // derrière le HTTPS et les cookies "secure" ne sont jamais renvoyés.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

        // Middleware globaux
        $middleware->append(CheckUserActive::class);

        // Middlewares nommés (alias)
        $middleware->alias([
            'recaptcha' => VerifyRecaptcha::class,
            'track.visit' => TrackSiteVisit::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('abonnements:check-expiration')->dailyAt('08:00');
        $schedule->command('visits:aggregate')->dailyAt('00:05');
        // Filet de sécurité si un webhook KomiPay n'arrive jamais (délivrance
        // ratée, timeout...) : sans ça, un paiement réellement réussi pouvait
        // rester bloqué "en_attente" indéfiniment, la commande n'étant
        // documentée que pour un lancement manuel.
        // Toutes les 5 min et non 15 : la fenêtre de validation KomiPay est de
        // 5 minutes (« Timeout transaction 5 minutes depassé »), un passage
        // toutes les 15 min laissait donc des paiements confirmés en attente
        // bien plus longtemps que nécessaire.
        $schedule->command('komipay:sync')->everyFiveMinutes();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();