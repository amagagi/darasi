<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * Reprend les 3 témoignages jusqu'ici codés en dur dans
 * landing_page.dart::_buildTestimonialsSection, comme données de démarrage.
 *
 * VOLONTAIREMENT ABSENT de DatabaseSeeder — voir AnnoncesTableSeeder. À lancer
 * explicitement :
 *
 *     php artisan db:seed --class=TestimonialsTableSeeder
 */
class TestimonialsTableSeeder extends Seeder
{
    public function run(): void
    {
        $temoignages = [
            [
                'author_name' => 'Mariama Salifou',
                'author_role' => 'Développeuse Mobile',
                'content' => 'Grâce à DARASI, j\'ai pu apprendre Flutter à mon rythme depuis Niamey. Le diplôme m\'a permis d\'obtenir mon premier stage.',
                'rating' => 5,
                'display_order' => 10,
            ],
            [
                'author_name' => 'Ibrahim Issa',
                'author_role' => 'Étudiant en Licence',
                'content' => 'Les supports PDF légers et l\'accès bas débit sont parfaits. J\'ai validé mon module de PHP facilement.',
                'rating' => 5,
                'display_order' => 20,
            ],
            [
                'author_name' => 'Fatimata Amadou',
                'author_role' => 'Professionnelle IT',
                'content' => 'L\'abonnement par catégorie est idéal. J\'ai pu me former à la fois en UX Design et en programmation.',
                'rating' => 4,
                'display_order' => 30,
            ],
        ];

        foreach ($temoignages as $donnees) {
            Testimonial::firstOrCreate(
                ['author_name' => $donnees['author_name']],
                $donnees + ['is_active' => true],
            );
        }
    }
}
