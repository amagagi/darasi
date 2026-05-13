<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
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

        // Formateur
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

        // Apprenant 1
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

        // Apprenant 2
        DB::table('users')->insert([
            'nom' => 'Jean',
            'prenom' => 'Dupont',
            'email' => 'jean.dupont@test.com',
            'telephone' => '+22790000003',
            'password' => Hash::make('password'),
            'role' => 'apprenant',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Apprenant 3
        DB::table('users')->insert([
            'nom' => 'Marie',
            'prenom' => 'Martin',
            'email' => 'marie.martin@test.com',
            'telephone' => '+22790000004',
            'password' => Hash::make('password'),
            'role' => 'apprenant',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}