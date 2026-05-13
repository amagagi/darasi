<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InscriptionsTableSeeder extends Seeder
{
    public function run(): void
    {
        // Apprenant id=3 inscrit au cours gratuit (id=3)
        DB::table('inscriptions')->insert([
            'apprenant_id' => 3,
            'cours_id' => 3,
            'progression' => 0,
            'statut' => 'actif',
            'date_debut' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Apprenant id=3 inscrit au cours payant (id=1) - paiement effectué
        DB::table('inscriptions')->insert([
            'apprenant_id' => 3,
            'cours_id' => 1,
            'progression' => 25.5,
            'statut' => 'actif',
            'date_debut' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Apprenant id=4 (Jean) inscrit au cours gratuit PHP (id=3)
        DB::table('inscriptions')->insert([
            'apprenant_id' => 4,
            'cours_id' => 3,
            'progression' => 50,
            'statut' => 'actif',
            'date_debut' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Apprenant id=5 (Marie) inscrit au cours Python (id=2) - paiement effectué
        DB::table('inscriptions')->insert([
            'apprenant_id' => 5,
            'cours_id' => 2,
            'progression' => 10,
            'statut' => 'actif',
            'date_debut' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}