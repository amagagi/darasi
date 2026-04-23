<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Illuminate\Support\Str;

class AppDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Catégories
        $categories = [
            ['nom' => 'Développement Mobile', 'pole_id' => 1],
            ['nom' => 'Développement Web', 'pole_id' => 1],
            ['nom' => 'Design & UX', 'pole_id' => 1],
            ['nom' => 'Mathématiques', 'pole_id' => 2],
            ['nom' => 'Physique', 'pole_id' => 2],
            ['nom' => 'Marketing', 'pole_id' => 3],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->updateOrInsert(['nom' => $cat['nom']], [
                'pole_id' => $cat['pole_id'],
                'slug' => Str::slug($cat['nom']),
                'ordre' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Récupérer les IDs
        $catMobile = DB::table('categories')->where('nom', 'Développement Mobile')->first()->id;
        $catWeb = DB::table('categories')->where('nom', 'Développement Web')->first()->id;
        $catMath = DB::table('categories')->where('nom', 'Mathématiques')->first()->id;

        $niveauIT = DB::table('niveaux')->where('pole_id', 1)->first()?->id ?? 1;
        $niveauScolaire = DB::table('niveaux')->where('pole_id', 2)->first()?->id ?? 1;

        // 2. Cours
        $cours = [
            [
                'titre' => 'Flutter Masterclass',
                'description' => 'Devenez un expert en développement mobile avec Flutter et Riverpod.',
                'pole_id' => 1,
                'categorie_id' => $catMobile,
                'niveau_id' => $niveauIT,
                'formateur_id' => 2,
                'prix' => 45000,
                'est_gratuit' => false,
                'statut' => 'publie',
            ],
            [
                'titre' => 'Laravel 11 Pro',
                'description' => 'Apprenez à construire des APIs robustes et des backends modernes.',
                'pole_id' => 1,
                'categorie_id' => $catWeb,
                'niveau_id' => $niveauIT,
                'formateur_id' => 2,
                'prix' => 35000,
                'est_gratuit' => false,
                'statut' => 'publie',
            ],
            [
                'titre' => 'Algèbre pour Débutants',
                'description' => 'Comprendre les bases de l\'algèbre pour le lycée.',
                'pole_id' => 2,
                'categorie_id' => $catMath,
                'niveau_id' => $niveauScolaire,
                'formateur_id' => 6,
                'prix' => 0,
                'est_gratuit' => true,
                'statut' => 'publie',
            ],
        ];

        foreach ($cours as $c) {
            DB::table('cours')->updateOrInsert(['titre' => $c['titre']], array_merge($c, [
                'objectifs' => 'Objectif 1\nObjectif 2',
                'prerequis' => 'Prérequis 1\nPrérequis 2',
                'image_couverture' => 'https://images.unsplash.com/photo-1587620962725-abab7fe55159?w=800',
                'created_at' => now(),
                'updated_at' => now(),
                'published_at' => now(),
            ]));
            
            $courseId = DB::table('cours')->where('titre', $c['titre'])->first()->id;

            // 3. Modules
            for ($i = 1; $i <= 3; $i++) {
                $moduleId = DB::table('modules')->insertGetId([
                    'cours_id' => $courseId,
                    'titre' => "Module $i : Introduction au sujet",
                    'description' => "Description détaillée du module $i",
                    'ordre' => $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 4. Leçons
                for ($j = 1; $j <= 2; $j++) {
                    DB::table('lecons')->insert([
                        'module_id' => $moduleId,
                        'titre' => "Leçon $j : Les bases",
                        'type_contenu' => 'video',
                        'contenu_text' => 'Contenu de la leçon en texte...',
                        'url_video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                        'ordre' => $j,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
