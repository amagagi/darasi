<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemandesFormationTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('demandes_formation')->insert([
            // Demande 1 - en attente
            [
                'nom' => 'Jean Dupont',
                'email' => 'jean.dupont@test.com',
                'telephone' => '+22790000003',
                'titre_cours_souhaite' => 'React Native',
                'description' => 'Je souhaite suivre une formation sur React Native pour développer des apps mobiles.',
                'domaine' => 'IT',
                'niveau_souhaite' => 'Intermédiaire',
                'statut' => 'en_attente',
                'traite_le' => null,
                'traite_par' => null,
                'commentaire_admin' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Demande 2 - prise en compte
            [
                'nom' => 'Marie Martin',
                'email' => 'marie.martin@test.com',
                'telephone' => '+22790000004',
                'titre_cours_souhaite' => 'Marketing Digital',
                'description' => 'Formation sur le marketing digital pour mon entreprise.',
                'domaine' => 'Marketing',
                'niveau_souhaite' => 'Débutant',
                'statut' => 'prise_en_compte',
                'traite_le' => now(),
                'traite_par' => 1,
                'commentaire_admin' => 'Demande prise en compte, nous allons étudier la faisabilité',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}