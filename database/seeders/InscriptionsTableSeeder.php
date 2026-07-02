<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InscriptionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $inscriptions = [
            [
                'apprenant_id' => 3,
                'cours_id' => 3,
                'progression' => 0,
                'statut' => 'actif',
                'date_debut' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'apprenant_id' => 3,
                'cours_id' => 1,
                'progression' => 25.5,
                'statut' => 'actif',
                'date_debut' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'apprenant_id' => 4,
                'cours_id' => 3,
                'progression' => 50,
                'statut' => 'actif',
                'date_debut' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'apprenant_id' => 5,
                'cours_id' => 2,
                'progression' => 10,
                'statut' => 'actif',
                'date_debut' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($inscriptions as $inscription) {
            DB::table('inscriptions')->updateOrInsert(
                [
                    'apprenant_id' => $inscription['apprenant_id'],
                    'cours_id' => $inscription['cours_id'],
                ], // Condition : vérifier par apprenant_id + cours_id (clé unique)
                $inscription // Données à insérer ou mettre à jour
            );
        }
    }
}