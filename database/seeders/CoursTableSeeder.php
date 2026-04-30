<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoursTableSeeder extends Seeder
{
    public function run(): void
    {
        // Cours 1: Laravel Débutant (payant)
        DB::table('cours')->insert([
            'titre' => 'Laravel Débutant',
            'description' => 'Apprenez les bases du framework Laravel pour créer des applications web modernes.',
            'objectifs' => 'Comprendre l\'architecture MVC, maîtriser les routes, les contrôleurs et les vues',
            'prerequis' => 'Connaissances de base en PHP',
            'pole_id' => 1,
            'formateur_id' => 2,
            'categorie_id' => 1,
            'statut' => 'publie',
            'prix' => 5000,
            'est_gratuit' => false,
            'est_certifiant' => true,
            'note_minimale_certificat' => 70,
            'created_at' => now(),
            'updated_at' => now(),
            'published_at' => now(),
        ]);

        // Cours 2: Python pour Data Science (payant)
        DB::table('cours')->insert([
            'titre' => 'Python pour Data Science',
            'description' => 'Découvrez Python et ses bibliothèques pour l\'analyse de données.',
            'objectifs' => 'Maîtriser Pandas, NumPy et Matplotlib',
            'prerequis' => 'Bases de programmation',
            'pole_id' => 1,
            'formateur_id' => 2,
            'categorie_id' => 2,
            'statut' => 'publie',
            'prix' => 8000,
            'est_gratuit' => false,
            'est_certifiant' => true,
            'note_minimale_certificat' => 75,
            'created_at' => now(),
            'updated_at' => now(),
            'published_at' => now(),
        ]);

        // Cours 3: Introduction à PHP (gratuit)
        DB::table('cours')->insert([
            'titre' => 'Introduction à PHP',
            'description' => 'Découvrez les bases de PHP pour débuter en programmation web.',
            'objectifs' => 'Comprendre la syntaxe PHP, les variables, les fonctions',
            'prerequis' => 'Aucun',
            'pole_id' => 1,
            'formateur_id' => 2,
            'categorie_id' => 1,
            'statut' => 'publie',
            'prix' => 0,
            'est_gratuit' => true,
            'est_certifiant' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'published_at' => now(),
        ]);

        // Cours 4: Préparation BAC Maths (gratuit)
        DB::table('cours')->insert([
            'titre' => 'Préparation BAC - Mathématiques',
            'description' => 'Réussissez votre épreuve de mathématiques au BAC.',
            'objectifs' => 'Maîtriser les notions clés pour le BAC',
            'prerequis' => 'Niveau première',
            'pole_id' => 2,
            'formateur_id' => 2,
            'categorie_id' => 4,
            'statut' => 'publie',
            'prix' => 0,
            'est_gratuit' => true,
            'est_certifiant' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'published_at' => now(),
        ]);
    }
}