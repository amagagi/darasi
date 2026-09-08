<?php

namespace Database\Seeders;

use App\Models\SiteStatistic;
use Illuminate\Database\Seeder;

/**
 * Chiffres clés de la vitrine, tous CALCULÉS à partir des données réelles.
 *
 * Aucune valeur n'est saisie à la main : `value_source` impose le calcul, ce
 * qui rend impossible d'afficher un chiffre sans fondement. Les libellés,
 * l'ordre et la visibilité restent modifiables depuis le back-office.
 *
 * Volontairement ABSENT de DatabaseSeeder (qui vide 32 tables). À lancer :
 *
 *     php artisan db:seed --class=SiteStatistiquesTableSeeder --force
 *
 * `firstOrCreate` sur le libellé : relancer ne duplique rien et ne réécrase
 * pas un réglage modifié depuis l'administration.
 */
class SiteStatistiquesTableSeeder extends Seeder
{
    public function run(): void
    {
        $chiffres = [
            [
                'label' => 'Apprenants inscrits',
                'value_source' => SiteStatistic::SOURCE_APPRENANTS_INSCRITS,
                'icon' => 'heroicon-o-users',
                'display_order' => 1,
            ],
            [
                'label' => 'Apprenants formés',
                'value_source' => SiteStatistic::SOURCE_APPRENANTS_FORMES,
                'icon' => 'heroicon-o-check-badge',
                'display_order' => 2,
            ],
            [
                'label' => 'Cours publiés',
                'value_source' => SiteStatistic::SOURCE_COURS_PUBLIES,
                'icon' => 'heroicon-o-academic-cap',
                'display_order' => 3,
            ],
            [
                'label' => 'Visiteurs du site',
                'value_source' => SiteStatistic::SOURCE_VISITEURS,
                'icon' => 'heroicon-o-globe-alt',
                'display_order' => 4,
            ],
            [
                'label' => 'Certificats délivrés',
                'value_source' => SiteStatistic::SOURCE_CERTIFICATS,
                'icon' => 'heroicon-o-trophy',
                'display_order' => 5,
            ],
        ];

        foreach ($chiffres as $chiffre) {
            SiteStatistic::firstOrCreate(
                ['label' => $chiffre['label']],
                $chiffre + [
                    // Jamais affichée : `value_source` prend le dessus. Reste
                    // vide pour qu'aucun chiffre inventé ne traîne en base.
                    'value' => '',
                    'is_active' => true,
                ],
            );
        }
    }
}
