<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Pôle IT (id=1)
            [
                'pole_id' => 1,
                'nom' => 'Programmation',
                'description' => 'Langages et frameworks',
                'slug' => 'programmation',
                'ordre' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pole_id' => 1,
                'nom' => 'Data Science',
                'description' => 'Analyse de données et IA',
                'slug' => 'data-science',
                'ordre' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pole_id' => 1,
                'nom' => 'Design Web',
                'description' => 'UI/UX et design',
                'slug' => 'design-web',
                'ordre' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Pôle Scolaire (id=2)
            [
                'pole_id' => 2,
                'nom' => 'Mathématiques',
                'description' => 'Cours de maths',
                'slug' => 'mathematiques',
                'ordre' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pole_id' => 2,
                'nom' => 'Français',
                'description' => 'Cours de français',
                'slug' => 'francais',
                'ordre' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Pôle Etudiant (id=3)
            [
                'pole_id' => 3,
                'nom' => 'Informatique',
                'description' => 'Cours informatique universitaire',
                'slug' => 'informatique',
                'ordre' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ===== NOUVELLES CATÉGORIES (Pôle IT) =====
            [
                'pole_id' => 1,
                'nom' => 'IA',
                'description' => 'Intelligence Artificielle et Machine Learning',
                'slug' => 'ia',
                'ordre' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pole_id' => 1,
                'nom' => 'Data Protection',
                'description' => 'Protection des données et RGPD',
                'slug' => 'data-protection',
                'ordre' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pole_id' => 1,
                'nom' => 'ITIL',
                'description' => 'Gestion des services informatiques',
                'slug' => 'itil',
                'ordre' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pole_id' => 1,
                'nom' => 'Développement Web',
                'description' => 'Création de sites et applications web',
                'slug' => 'developpement-web',
                'ordre' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pole_id' => 1,
                'nom' => 'Cybersécurité',
                'description' => 'Sécurité des systèmes d\'information',
                'slug' => 'cybersecurite',
                'ordre' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($categories as $categorie) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $categorie['slug']],
                $categorie
            );
        }
    }
}