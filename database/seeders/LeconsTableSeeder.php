<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeconsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('lecons')->insert([
            // Module 1 - Leçon 1 (vidéo)
            [
                'module_id' => 1,
                'titre' => 'Installation de Laravel',
                'type_contenu' => 'video',
                'url_video' => '/storage/videos/installation-laravel.mp4',
                'url_pdf' => null,
                'contenu_text' => null,
                'duree_video' => 600,
                'ordre' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Module 1 - Leçon 2 (pdf)
            [
                'module_id' => 1,
                'titre' => 'Structure d\'un projet Laravel',
                'type_contenu' => 'pdf',
                'url_video' => null,
                'url_pdf' => '/storage/cours/structure-laravel.pdf',
                'contenu_text' => null,
                'duree_video' => null,
                'ordre' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Module 1 - Leçon 3 (vidéo)
            [
                'module_id' => 1,
                'titre' => 'Premier contrôleur',
                'type_contenu' => 'video',
                'url_video' => '/storage/videos/premier-controleur.mp4',
                'url_pdf' => null,
                'contenu_text' => null,
                'duree_video' => 900,
                'ordre' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Module 2 - Leçon 1 (vidéo)
            [
                'module_id' => 2,
                'titre' => 'Les routes de base',
                'type_contenu' => 'video',
                'url_video' => '/storage/videos/routes-base.mp4',
                'url_pdf' => null,
                'contenu_text' => null,
                'duree_video' => 450,
                'ordre' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Module 2 - Leçon 2 (pdf)
            [
                'module_id' => 2,
                'titre' => 'Les contrôleurs RESTful',
                'type_contenu' => 'pdf',
                'url_video' => null,
                'url_pdf' => '/storage/cours/controleurs-restful.pdf',
                'contenu_text' => null,
                'duree_video' => null,
                'ordre' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Module 3 - Leçon 1 (vidéo)
            [
                'module_id' => 3,
                'titre' => 'Introduction à Blade',
                'type_contenu' => 'video',
                'url_video' => '/storage/videos/introduction-blade.mp4',
                'url_pdf' => null,
                'contenu_text' => null,
                'duree_video' => 500,
                'ordre' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Module 3 - Leçon 2 (article)
            [
                'module_id' => 3,
                'titre' => 'Les directives Blade',
                'type_contenu' => 'article',
                'url_video' => null,
                'url_pdf' => null,
                'contenu_text' => 'Les directives Blade sont des mots-clés qui permettent d\'exécuter du PHP dans les vues...',
                'duree_video' => null,
                'ordre' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Module 4 - Leçon 1 (vidéo)
            [
                'module_id' => 4,
                'titre' => 'Introduction à Eloquent',
                'type_contenu' => 'video',
                'url_video' => '/storage/videos/introduction-eloquent.mp4',
                'url_pdf' => null,
                'contenu_text' => null,
                'duree_video' => 600,
                'ordre' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Module 4 - Leçon 2 (pdf)
            [
                'module_id' => 4,
                'titre' => 'Les relations Eloquent',
                'type_contenu' => 'pdf',
                'url_video' => null,
                'url_pdf' => '/storage/cours/relations-eloquent.pdf',
                'contenu_text' => null,
                'duree_video' => null,
                'ordre' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Module 5 - Leçon 1 (vidéo)
            [
                'module_id' => 5,
                'titre' => 'Installation de Python',
                'type_contenu' => 'video',
                'url_video' => '/storage/videos/installation-python.mp4',
                'url_pdf' => null,
                'contenu_text' => null,
                'duree_video' => 300,
                'ordre' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Module 5 - Leçon 2 (article)
            [
                'module_id' => 5,
                'titre' => 'Premiers pas avec Python',
                'type_contenu' => 'article',
                'url_video' => null,
                'url_pdf' => null,
                'contenu_text' => 'Python est un langage de programmation interprété, orienté objet, et multi-paradigme. Il est particulièrement apprécié pour sa syntaxe claire et sa lisibilité.',
                'duree_video' => null,
                'ordre' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}