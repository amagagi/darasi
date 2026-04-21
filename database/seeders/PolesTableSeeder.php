<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PolesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('poles')->insert([
            [
                'nom' => 'IT',
                'description' => 'Cours d\'informatique et technologies',
                'slug' => 'it',
                'ordre' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Scolaire',
                'description' => 'Préparation examens CFEPD, BEPC, BAC',
                'slug' => 'scolaire',
                'ordre' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Etudiant',
                'description' => 'Renforcement universitaire',
                'slug' => 'etudiant',
                'ordre' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}