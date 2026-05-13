<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulesTableSeeder extends Seeder
{
    public function run(): void
    {
        // Modules pour cours Laravel (id=1)
        DB::table('modules')->insert([
            ['cours_id' => 1, 'titre' => 'Introduction à Laravel', 'description' => 'Découvrez Laravel et son écosystème', 'ordre' => 1, 'duree_estimee' => 60, 'created_at' => now(), 'updated_at' => now()],
            ['cours_id' => 1, 'titre' => 'Routing et Contrôleurs', 'description' => 'Gérez les routes et les contrôleurs', 'ordre' => 2, 'duree_estimee' => 90, 'created_at' => now(), 'updated_at' => now()],
            ['cours_id' => 1, 'titre' => 'Blade Templates', 'description' => 'Créez des vues dynamiques avec Blade', 'ordre' => 3, 'duree_estimee' => 60, 'created_at' => now(), 'updated_at' => now()],
            ['cours_id' => 1, 'titre' => 'Eloquent ORM', 'description' => 'Manipulez la base de données avec Eloquent', 'ordre' => 4, 'duree_estimee' => 90, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Modules pour cours Python (id=2)
        DB::table('modules')->insert([
            ['cours_id' => 2, 'titre' => 'Introduction à Python', 'description' => 'Les bases de Python', 'ordre' => 1, 'duree_estimee' => 60, 'created_at' => now(), 'updated_at' => now()],
            ['cours_id' => 2, 'titre' => 'Pandas pour l\'analyse', 'description' => 'Manipulation de données avec Pandas', 'ordre' => 2, 'duree_estimee' => 120, 'created_at' => now(), 'updated_at' => now()],
            ['cours_id' => 2, 'titre' => 'Visualisation avec Matplotlib', 'description' => 'Créez des graphiques', 'ordre' => 3, 'duree_estimee' => 60, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}