<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;

/**
 * Jeu de plateformes de démonstration.
 *
 * VOLONTAIREMENT ABSENT de DatabaseSeeder — voir AnnoncesTableSeeder. À lancer
 * explicitement :
 *
 *     php artisan db:seed --class=PlatformsTableSeeder
 */
class PlatformsTableSeeder extends Seeder
{
    public function run(): void
    {
        $plateformes = [
            [
                'name' => 'DARASI LMS',
                'slug' => 'darasi-lms',
                'short_description' => 'La plateforme d\'apprentissage en ligne DARASI : cours, quiz et certificats.',
                'description' => '<p>La plateforme historique DARASI, dédiée à la formation en ligne : cours vidéo, tests d\'évaluation et certificats vérifiables.</p>',
                'url' => 'https://darasihub.com',
                'category' => 'e-learning',
                'display_order' => 10,
            ],
            [
                'name' => 'DARASI Admin',
                'slug' => 'darasi-admin',
                'short_description' => 'Back-office de gestion des cours, apprenants et formateurs.',
                'description' => '<p>L\'espace d\'administration réservé aux équipes DARASI pour piloter le catalogue de formations et le suivi pédagogique.</p>',
                'url' => 'https://darasihub.com/admin',
                'category' => 'gestion',
                'display_order' => 20,
            ],
        ];

        foreach ($plateformes as $donnees) {
            // firstOrCreate sur le slug : relancer le seeder ne duplique rien
            // et n'écrase pas les réglages modifiés depuis le back-office.
            Platform::firstOrCreate(
                ['slug' => $donnees['slug']],
                $donnees + ['is_active' => true],
            );
        }
    }
}
