<?php

namespace Database\Seeders;

use App\Models\TestFinal;
use Illuminate\Database\Seeder;

class TestsFinauxTableSeeder extends Seeder
{
    public function run(): void
    {
        $testsFinaux = [
            [
                'cours_id' => 1,
                'titre' => 'Test final - Laravel',
                'description' => 'Test final du cours Laravel Débutant',
                'note_minimale' => 70.00,
                'duree_limite' => 45,
            ],
            [
                'cours_id' => 2,
                'titre' => 'Test final - Python Data Science',
                'description' => 'Test final du cours Python pour Data Science',
                'note_minimale' => 75.00,
                'duree_limite' => 45,
            ],
            [
                'cours_id' => 5,
                'titre' => 'Test final - Database',
                'description' => 'Test final du cours Database SGBD',
                'note_minimale' => 70.00,
                'duree_limite' => 30,
            ],
        ];

        foreach ($testsFinaux as $test) {
            TestFinal::updateOrCreate(
                ['cours_id' => $test['cours_id']],
                $test
            );
        }
    }
}