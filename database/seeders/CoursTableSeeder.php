<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoursTableSeeder extends Seeder
{
    public function run(): void
    {
        // Le formateur de démo peut avoir été supprimé (compte de test nettoyé
        // en prod). Dans ce cas on saute ce seeder au lieu de planter sur la
        // contrainte de clé étrangère cours.formateur_id -> users.id.
        $formateurId = DB::table('users')->where('email', 'formateur@darasi.com')->value('id');

        if ($formateurId === null) {
            $this->command?->warn(
                'CoursTableSeeder ignoré : aucun utilisateur formateur@darasi.com trouvé.'
            );

            return;
        }

        $cours = [
            [
                'titre' => 'Laravel Débutant',
                'description' => 'Apprenez les bases du framework Laravel pour créer des applications web modernes.',
                'objectifs' => 'Comprendre l\'architecture MVC, maîtriser les routes, les contrôleurs et les vues',
                'prerequis' => 'Connaissances de base en PHP',
                'pole_id' => 1,
                'categorie_id' => 1,
                'statut' => 'publie',
                'prix' => 5000,
                'est_gratuit' => false,
                'est_certifiant' => true,
                'note_minimale_certificat' => 70,
                'published_at' => now(),
            ],
            [
                'titre' => 'Python pour Data Science',
                'description' => 'Découvrez Python et ses bibliothèques pour l\'analyse de données.',
                'objectifs' => 'Maîtriser Pandas, NumPy et Matplotlib',
                'prerequis' => 'Bases de programmation',
                'pole_id' => 1,
                'categorie_id' => 2,
                'statut' => 'publie',
                'prix' => 8000,
                'est_gratuit' => false,
                'est_certifiant' => true,
                'note_minimale_certificat' => 75,
                'published_at' => now(),
            ],
            [
                'titre' => 'Introduction à PHP',
                'description' => 'Découvrez les bases de PHP pour débuter en programmation web.',
                'objectifs' => 'Comprendre la syntaxe PHP, les variables, les fonctions',
                'prerequis' => 'Aucun',
                'pole_id' => 1,
                'categorie_id' => 1,
                'statut' => 'publie',
                'prix' => 0,
                'est_gratuit' => true,
                'est_certifiant' => false,
                'published_at' => now(),
            ],
            [
                'titre' => 'Préparation BAC - Mathématiques',
                'description' => 'Réussissez votre épreuve de mathématiques au BAC.',
                'objectifs' => 'Maîtriser les notions clés pour le BAC',
                'prerequis' => 'Niveau première',
                'pole_id' => 2,
                'categorie_id' => 4,
                'statut' => 'publie',
                'prix' => 0,
                'est_gratuit' => true,
                'est_certifiant' => false,
                'published_at' => now(),
            ],
        ];

        foreach ($cours as $unCours) {
            DB::table('cours')->updateOrInsert(
                ['titre' => $unCours['titre']], // Condition : un cours de démo par titre
                array_merge($unCours, [
                    'formateur_id' => $formateurId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}