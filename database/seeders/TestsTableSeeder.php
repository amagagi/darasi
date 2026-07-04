<?php

namespace Database\Seeders;

use App\Models\Test;
use Illuminate\Database\Seeder;

class TestsTableSeeder extends Seeder
{
    public function run(): void
    {
        $tests = [
            [
                'module_id' => 1,
                'titre' => 'Test Laravel - Introduction',
                'description' => 'Évaluez vos connaissances sur les bases de Laravel',
                'ordre' => 1,
                'duree_limite' => 15,
            ],
            [
                'module_id' => 2,
                'titre' => 'Test Laravel - Routing',
                'description' => 'Test sur les routes et contrôleurs',
                'ordre' => 2,
                'duree_limite' => 20,
            ],
            [
                'module_id' => 3,
                'titre' => 'Test Laravel - Blade',
                'description' => 'Test sur les templates Blade',
                'ordre' => 3,
                'duree_limite' => 15,
            ],
            [
                'module_id' => 4,
                'titre' => 'Test Laravel - Eloquent',
                'description' => 'Test sur Eloquent ORM',
                'ordre' => 4,
                'duree_limite' => 25,
            ],
        ];

        foreach ($tests as $test) {
            Test::updateOrCreate(
                ['titre' => $test['titre'], 'module_id' => $test['module_id']],
                $test
            );
        }
    }
}