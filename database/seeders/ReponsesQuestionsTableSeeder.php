<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReponsesQuestionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $reponses = [
            [
                'id' => 1,
                'tentative_test_id' => 1,
                'tentative_final_id' => null,
                'question_id' => 3, // MVC (question ouverte)
                'reponse_texte' => 'Le pattern MVC sépare l\'application en trois composants principaux : Modèle (données), Vue (interface utilisateur) et Contrôleur (logique métier).',
                'choix_id' => null,
                'est_correcte' => false,
                'points_obtenus' => 0,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'id' => 2,
                'tentative_test_id' => 2,
                'tentative_final_id' => null,
                'question_id' => 3,
                'reponse_texte' => 'MVC veut dire Modèle Vue Contrôleur. Le Modèle fait le lien avec la base de données, la Vue affiche la page HTML, et le Contrôleur fait le traitement intermédiaire.',
                'choix_id' => null,
                'est_correcte' => false,
                'points_obtenus' => 0,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
        ];

        foreach ($reponses as $reponse) {
            DB::table('reponses_questions')->updateOrInsert(
                ['id' => $reponse['id']],
                $reponse
            );
        }
    }
}
