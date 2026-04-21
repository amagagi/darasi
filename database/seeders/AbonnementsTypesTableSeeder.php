<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbonnementsTypesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('abonnements_types')->insert([
            [
                'nom' => 'Abonnement Mensuel',
                'description' => 'Accès illimité à tous les cours pendant 30 jours',
                'duree_jours' => 30,
                'prix' => 5000,
                'est_populaire' => true,
                'est_actif' => true,
                'ordre' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Abonnement Trimestriel',
                'description' => 'Accès illimité à tous les cours pendant 90 jours',
                'duree_jours' => 90,
                'prix' => 12000,
                'est_populaire' => false,
                'est_actif' => true,
                'ordre' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Abonnement Annuel',
                'description' => 'Accès illimité à tous les cours pendant 365 jours',
                'duree_jours' => 365,
                'prix' => 40000,
                'est_populaire' => true,
                'est_actif' => true,
                'ordre' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}