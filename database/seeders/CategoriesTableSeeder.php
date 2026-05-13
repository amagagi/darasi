<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            // Pôle IT (id=1)
            ['pole_id' => 1, 'nom' => 'Programmation', 'description' => 'Langages et frameworks', 'slug' => 'programmation', 'ordre' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['pole_id' => 1, 'nom' => 'Data Science', 'description' => 'Analyse de données et IA', 'slug' => 'data-science', 'ordre' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['pole_id' => 1, 'nom' => 'Design Web', 'description' => 'UI/UX et design', 'slug' => 'design-web', 'ordre' => 3, 'created_at' => now(), 'updated_at' => now()],
            // Pôle Scolaire (id=2)
            ['pole_id' => 2, 'nom' => 'Mathématiques', 'description' => 'Cours de maths', 'slug' => 'mathematiques', 'ordre' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['pole_id' => 2, 'nom' => 'Français', 'description' => 'Cours de français', 'slug' => 'francais', 'ordre' => 2, 'created_at' => now(), 'updated_at' => now()],
            // Pôle Etudiant (id=3)
            ['pole_id' => 3, 'nom' => 'Informatique', 'description' => 'Cours informatique universitaire', 'slug' => 'informatique', 'ordre' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}