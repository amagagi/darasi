<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

/**
 * Jeu de partenaires de démonstration.
 *
 * VOLONTAIREMENT ABSENT de DatabaseSeeder — voir AnnoncesTableSeeder pour le
 * pourquoi (le seeding automatique au démarrage du conteneur a été retiré
 * après un incident de production). À lancer explicitement :
 *
 *     php artisan db:seed --class=PartnersTableSeeder
 */
class PartnersTableSeeder extends Seeder
{
    public function run(): void
    {
        $partenaires = [
            [
                'name' => 'Université Abdou Moumouni',
                'logo_path' => 'partners/uam.svg',
                'website_url' => 'https://uam.refer.ne',
                'description' => 'Partenaire académique historique.',
                'display_order' => 10,
            ],
            [
                'name' => 'Ministère de l\'Enseignement Supérieur',
                'logo_path' => 'partners/mes.svg',
                'website_url' => null,
                'description' => 'Partenaire institutionnel.',
                'display_order' => 20,
            ],
            [
                'name' => 'Chambre de Commerce du Niger',
                'logo_path' => 'partners/ccian.svg',
                'website_url' => 'https://ccian.ne',
                'description' => 'Partenaire pour l\'insertion professionnelle.',
                'display_order' => 30,
            ],
            [
                'name' => 'Orange Niger',
                'logo_path' => 'partners/orange.svg',
                'website_url' => 'https://orange.ne',
                'description' => 'Partenaire technologique.',
                'display_order' => 40,
            ],
        ];

        foreach ($partenaires as $donnees) {
            // firstOrCreate sur le nom : relancer le seeder ne duplique rien et
            // n'écrase pas les réglages modifiés depuis le back-office.
            Partner::firstOrCreate(
                ['name' => $donnees['name']],
                $donnees + ['is_active' => true],
            );
        }
    }
}
