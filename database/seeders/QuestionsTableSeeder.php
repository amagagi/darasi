<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            // Questions pour le test de module (test_id = 1)
            [
                'test_id' => 1,
                'test_final_id' => null,
                'question' => 'Qu\'est-ce que Laravel ?',
                'type' => 'qcm',
                'points' => 2,
                'ordre' => 1,
            ],
            [
                'test_id' => 1,
                'test_final_id' => null,
                'question' => 'Quelle est la commande pour créer un contrôleur dans Laravel ?',
                'type' => 'qcm',
                'points' => 2,
                'ordre' => 2,
            ],
            [
                'test_id' => 1,
                'test_final_id' => null,
                'question' => 'Expliquez le principe du MVC dans Laravel.',
                'type' => 'ouverte',
                'points' => 4,
                'ordre' => 3,
            ],
            // Questions pour le test final (test_final_id = 1)
            [
                'test_id' => null,
                'test_final_id' => 1,
                'question' => 'Quelle est la commande pour lancer le serveur de développement Laravel ?',
                'type' => 'qcm',
                'points' => 2,
                'ordre' => 1,
            ],
            [
                'test_id' => null,
                'test_final_id' => 1,
                'question' => 'Quel est le nom du système de templates par défaut dans Laravel ?',
                'type' => 'qcm',
                'points' => 2,
                'ordre' => 2,
            ],
            [
                'test_id' => null,
                'test_final_id' => 1,
                'question' => 'Décrivez comment fonctionne Eloquent ORM.',
                'type' => 'ouverte',
                'points' => 5,
                'ordre' => 3,
            ],
        ];

        foreach ($questions as $question) {
            Question::updateOrCreate(
                [
                    'question' => $question['question'],
                    'test_id' => $question['test_id'],
                    'test_final_id' => $question['test_final_id'],
                ],
                $question
            );
        }
    }
}