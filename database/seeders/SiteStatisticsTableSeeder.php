<?php

namespace Database\Seeders;

use App\Models\SiteStatistic;
use Illuminate\Database\Seeder;

/**
 * Chiffres clés de démonstration.
 *
 * VOLONTAIREMENT ABSENT de DatabaseSeeder — voir AnnoncesTableSeeder. À lancer
 * explicitement :
 *
 *     php artisan db:seed --class=SiteStatisticsTableSeeder
 */
class SiteStatisticsTableSeeder extends Seeder
{
    public function run(): void
    {
        $statistiques = [
            ['label' => 'Apprenants formés', 'value' => '1200+', 'icon' => 'heroicon-o-users', 'display_order' => 10],
            ['label' => 'Formations disponibles', 'value' => '15+', 'icon' => 'heroicon-o-academic-cap', 'display_order' => 20],
            ['label' => 'Taux de satisfaction', 'value' => '98%', 'icon' => 'heroicon-o-star', 'display_order' => 30],
            ['label' => 'Années d\'expérience', 'value' => '5', 'icon' => 'heroicon-o-calendar', 'display_order' => 40],
        ];

        foreach ($statistiques as $donnees) {
            SiteStatistic::firstOrCreate(
                ['label' => $donnees['label']],
                $donnees + ['is_active' => true],
            );
        }
    }
}
