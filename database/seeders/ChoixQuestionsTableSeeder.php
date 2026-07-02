<?php

namespace Database\Seeders;

use App\Models\ChoixQuestion;
use App\Models\Question;
use Illuminate\Database\Seeder;

class ChoixQuestionsTableSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer les questions QCM
        $questionsQcm = Question::where('type', 'qcm')->get();

        $choix = [
            // Question 1 : Qu'est-ce que Laravel ?
            [
                'question_id' => $questionsQcm->firstWhere('id', 1)?->id ?? 1,
                'texte' => 'Un framework PHP pour le développement web',
                'est_correct' => true,
                'ordre' => 1,
            ],
            [
                'question_id' => $questionsQcm->firstWhere('id', 1)?->id ?? 1,
                'texte' => 'Un langage de programmation',
                'est_correct' => false,
                'ordre' => 2,
            ],
            [
                'question_id' => $questionsQcm->firstWhere('id', 1)?->id ?? 1,
                'texte' => 'Un système d\'exploitation',
                'est_correct' => false,
                'ordre' => 3,
            ],
            // Question 2 : Commande pour créer un contrôleur
            [
                'question_id' => $questionsQcm->firstWhere('id', 2)?->id ?? 2,
                'texte' => 'php artisan make:controller NomController',
                'est_correct' => true,
                'ordre' => 1,
            ],
            [
                'question_id' => $questionsQcm->firstWhere('id', 2)?->id ?? 2,
                'texte' => 'php artisan create:controller NomController',
                'est_correct' => false,
                'ordre' => 2,
            ],
            [
                'question_id' => $questionsQcm->firstWhere('id', 2)?->id ?? 2,
                'texte' => 'php artisan controller:make NomController',
                'est_correct' => false,
                'ordre' => 3,
            ],
            // Question 4 : Commande pour lancer le serveur
            [
                'question_id' => $questionsQcm->firstWhere('id', 4)?->id ?? 4,
                'texte' => 'php artisan serve',
                'est_correct' => true,
                'ordre' => 1,
            ],
            [
                'question_id' => $questionsQcm->firstWhere('id', 4)?->id ?? 4,
                'texte' => 'php artisan start',
                'est_correct' => false,
                'ordre' => 2,
            ],
            [
                'question_id' => $questionsQcm->firstWhere('id', 4)?->id ?? 4,
                'texte' => 'php artisan run',
                'est_correct' => false,
                'ordre' => 3,
            ],
            // Question 5 : Système de templates Laravel
            [
                'question_id' => $questionsQcm->firstWhere('id', 5)?->id ?? 5,
                'texte' => 'Blade',
                'est_correct' => true,
                'ordre' => 1,
            ],
            [
                'question_id' => $questionsQcm->firstWhere('id', 5)?->id ?? 5,
                'texte' => 'Twig',
                'est_correct' => false,
                'ordre' => 2,
            ],
            [
                'question_id' => $questionsQcm->firstWhere('id', 5)?->id ?? 5,
                'texte' => 'Smarty',
                'est_correct' => false,
                'ordre' => 3,
            ],
        ];

        foreach ($choix as $choixItem) {
            ChoixQuestion::updateOrCreate(
                [
                    'question_id' => $choixItem['question_id'],
                    'texte' => $choixItem['texte'],
                ],
                $choixItem
            );
        }
    }
}