<?php

namespace Database\Seeders;

use App\Models\ContenuSite;
use App\Models\SiteVisit;
use App\Models\VisitCounter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Données synthétiques de visites sur les 30 derniers jours, pour ne pas
 * avoir un dashboard vide en local/démo. Seed aussi le réglage du compteur du
 * footer (contenus_site, clé ContenuSite::CLE_COMPTEUR_VISITES) à titre
 * d'exemple éditable — voir App\Http\Controllers\Api\SiteVisitController.
 *
 * VOLONTAIREMENT ABSENT de DatabaseSeeder — voir AnnoncesTableSeeder. À lancer
 * explicitement :
 *
 *     php artisan db:seed --class=SiteVisitsTableSeeder
 */
class SiteVisitsTableSeeder extends Seeder
{
    public function run(): void
    {
        $totalCumule = 0;

        for ($i = 29; $i >= 0; $i--) {
            $jour = today()->subDays($i);
            $visitesDuJour = random_int(15, 80);
            $totalCumule += $visitesDuJour;

            VisitCounter::updateOrCreate(
                ['date' => $jour->toDateString()],
                ['today_visits' => $visitesDuJour, 'total_visits' => $totalCumule],
            );

            // Quelques lignes de log par jour (pas une par visite comptée :
            // ce n'est que pour peupler le classement des pages, pas pour
            // que today_visits corresponde exactement au nombre de lignes).
            $pages = ['/welcome', '/plateformes', '/plateformes/darasi-lms'];
            foreach (range(1, min(5, $visitesDuJour)) as $n) {
                SiteVisit::create([
                    'visited_at' => $jour->copy()->addHours(random_int(7, 22)),
                    'ip_hash' => hash('sha256', Str::random(20)),
                    'user_agent' => 'Mozilla/5.0 (démo)',
                    'page_url' => $pages[array_rand($pages)],
                    'session_id' => Str::random(32),
                ]);
            }
        }

        ContenuSite::firstOrCreate(
            ['cle' => ContenuSite::CLE_COMPTEUR_VISITES],
            [
                'titre' => 'Compteur de visites (réglage technique)',
                'contenu' => '{n} visites',
                'ordre' => 999,
                'est_actif' => true,
            ],
        );
    }
}
