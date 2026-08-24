<?php

namespace App\Console\Commands;

use App\Models\SiteVisit;
use App\Models\VisitCounter;
use Illuminate\Console\Command;

/**
 * COMMANDE DE RECALCUL DES COMPTEURS DE VISITES
 *
 * Recalcule today_visits/total_visits sur les derniers jours à partir de
 * site_visits, et corrige toute dérive (le compteur en temps réel du
 * middleware TrackSiteVisit peut manquer un incrément en cas d'erreur
 * applicative isolée). "Visites" = sessions distinctes par jour
 * (COALESCE(session_id, ip_hash)), pas le nombre brut de pages vues.
 *
 * À exécuter via CRON tous les jours à 00h05 (déjà planifiée dans
 * bootstrap/app.php via withSchedule) :
 * 5 0 * * * cd /chemin/darasi && php artisan schedule:run >> storage/logs/cron.log 2>&1
 */
class AggregateVisitCounters extends Command
{
    protected $signature = 'visits:aggregate {--days=2 : Nombre de jours récents à recalculer}';

    protected $description = 'Recalcule les compteurs de visites des derniers jours et corrige les dérives éventuelles';

    public function handle(): int
    {
        $jours = max(1, (int) $this->option('days'));
        $premierJour = today()->subDays($jours - 1);

        $totalCumule = VisitCounter::query()
            ->where('date', '<', $premierJour)
            ->orderByDesc('date')
            ->value('total_visits') ?? 0;

        for ($date = $premierJour->copy(); $date->lte(today()); $date->addDay()) {
            $visitesDuJour = (int) SiteVisit::query()
                ->whereBetween('visited_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
                ->selectRaw('COUNT(DISTINCT COALESCE(session_id, ip_hash)) as total')
                ->value('total');

            $totalCumule += $visitesDuJour;

            VisitCounter::updateOrCreate(
                ['date' => $date->toDateString()],
                ['today_visits' => $visitesDuJour, 'total_visits' => $totalCumule],
            );

            $this->line("{$date->toDateString()} : {$visitesDuJour} visite(s), cumul {$totalCumule}");
        }

        $this->info('Compteurs de visites recalculés.');

        return self::SUCCESS;
    }
}
