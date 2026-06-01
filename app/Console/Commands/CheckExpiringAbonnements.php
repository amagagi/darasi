<?php
// app/Console/Commands/CheckExpiringAbonnements.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AbonnementSouscrit;
use App\Models\Notification;
use Carbon\Carbon;

/**
 * COMMANDE DE VÉRIFICATION DES ABONNEMENTS
 * 
 * Vérifie quotidiennement les abonnements qui expirent bientôt
 * Envoie des notifications aux apprenants concernés
 * 
 * @author amagagi
 * @version 1.0
 * 
 * À exécuter via CRON tous les jours à 8h:
 * 0 8 * * * cd /chemin/darasi && php artisan abonnements:check-expiration >> storage/logs/cron.log 2>&1
 */
class CheckExpiringAbonnements extends Command
{
    protected $signature = 'abonnements:check-expiration';
    protected $description = 'Vérifie les abonnements qui expirent bientôt et envoie des notifications';

    public function handle()
    {
        $this->info('🔍 Vérification des abonnements...');

        // 1. Abonnements qui expirent dans les 7 jours
        $expiringSoon = AbonnementSouscrit::where('statut', 'actif')
            ->where('date_fin', '>', Carbon::now())
            ->where('date_fin', '<', Carbon::now()->addDays(7))
            ->with(['apprenant', 'type', 'categorie'])
            ->get();

        $this->info("📊 {$expiringSoon->count()} abonnement(s) expirent bientôt");

        foreach ($expiringSoon as $abonnement) {
            $joursRestants = Carbon::now()->diffInDays($abonnement->date_fin);
            
            Notification::create([
                'user_id' => $abonnement->apprenant_id,
                'titre' => 'Votre abonnement expire bientôt',
                'message' => "Votre abonnement {$abonnement->type->nom} pour la catégorie '{$abonnement->categorie->nom}' expire dans {$joursRestants} jours. Renouvelez pour continuer à accéder aux cours.",
                'type' => 'abonnement',
                'data' => json_encode([
                    'abonnement_id' => $abonnement->id,
                    'jours_restants' => $joursRestants,
                    'date_fin' => $abonnement->date_fin->format('Y-m-d')
                ])
            ]);

            $this->info("✅ Notification envoyée pour abonnement #{$abonnement->id} (expire dans {$joursRestants} jours)");
        }

        // 2. Abonnements expirés (désactivation)
        $expired = AbonnementSouscrit::where('statut', 'actif')
            ->where('date_fin', '<', Carbon::now())
            ->update(['statut' => 'expire']);

        if ($expired > 0) {
            $this->info("⏰ {$expired} abonnement(s) ont été marqués comme expirés");
        }

        $this->info('✅ Vérification terminée');
    }
}