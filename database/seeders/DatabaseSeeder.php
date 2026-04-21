<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Créer un utilisateur admin
        DB::table('users')->insert([
            'nom' => 'Admin',
            'prenom' => 'System',
            'email' => 'admin@darasi.com',
            'telephone' => '+22790000000',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Créer un formateur
        DB::table('users')->insert([
            'nom' => 'Formateur',
            'prenom' => 'Test',
            'email' => 'formateur@darasi.com',
            'telephone' => '+22790000001',
            'password' => Hash::make('password'),
            'role' => 'formateur',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Créer un apprenant
        DB::table('users')->insert([
            'nom' => 'Apprenant',
            'prenom' => 'Test',
            'email' => 'apprenant@darasi.com',
            'telephone' => '+22790000002',
            'password' => Hash::make('password'),
            'role' => 'apprenant',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Appeler les autres seeders
        $this->call([
            PolesTableSeeder::class,
            NiveauxTableSeeder::class,
            AbonnementsTypesTableSeeder::class,
        ]);
    }
}