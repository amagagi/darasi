<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbonnementsSouscritsTableSeeder extends Seeder
{
    public function run(): void
    {
        $abonnements = [
            [
                'apprenant_id' => 3,
                'type_abonnement_id' => 1,
                'categorie_id' => 1,
                'date_debut' => now()->subDays(10),
                'date_fin' => now()->addDays(20),
                'statut' => 'actif',
                'paiement_id' => null,
                'created_at' => now()->subDays(10),
                'updated_at' => now(),
            ],
            [
                'apprenant_id' => 4,
                'type_abonnement_id' => 2,
                'categorie_id' => 1,
                'date_debut' => now()->subDays(5),
                'date_fin' => now()->addDays(85),
                'statut' => 'actif',
                'paiement_id' => null,
                'created_at' => now()->subDays(5),
                'updated_at' => now(),
            ],
            [
                'apprenant_id' => 5,
                'type_abonnement_id' => 3,
                'categorie_id' => 2,
                'date_debut' => now()->subMonths(2),
                'date_fin' => now()->addMonths(10),
                'statut' => 'actif',
                'paiement_id' => null,
                'created_at' => now()->subMonths(2),
                'updated_at' => now(),
            ],
            [
                'apprenant_id' => 3,
                'type_abonnement_id' => 1,
                'categorie_id' => 2,
                'date_debut' => now()->subMonths(6),
                'date_fin' => now()->subMonths(5),
                'statut' => 'expire',
                'paiement_id' => null,
                'created_at' => now()->subMonths(6),
                'updated_at' => now()->subMonths(5),
            ],
        ];

        foreach ($abonnements as $abonnement) {
            DB::table('abonnements_souscrits')->updateOrInsert(
                [
                    'apprenant_id' => $abonnement['apprenant_id'],
                    'type_abonnement_id' => $abonnement['type_abonnement_id'],
                    'categorie_id' => $abonnement['categorie_id'],
                    'date_debut' => $abonnement['date_debut'],
                ],
                $abonnement
            );
        }
    }
}