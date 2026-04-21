<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NiveauxTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('niveaux')->insert([
            // Pôle Scolaire (id=2)
            ['pole_id' => 2, 'libelle' => 'CFEPD', 'ordre' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['pole_id' => 2, 'libelle' => 'BEPC', 'ordre' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['pole_id' => 2, 'libelle' => 'BAC', 'ordre' => 3, 'created_at' => now(), 'updated_at' => now()],
            // Pôle Etudiant (id=3)
            ['pole_id' => 3, 'libelle' => 'Licence 1', 'ordre' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['pole_id' => 3, 'libelle' => 'Licence 2', 'ordre' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['pole_id' => 3, 'libelle' => 'Licence 3', 'ordre' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['pole_id' => 3, 'libelle' => 'Master 1', 'ordre' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['pole_id' => 3, 'libelle' => 'Master 2', 'ordre' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}