<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'nom' => 'Admin',
                'prenom' => 'System',
                'email' => 'admin@darasi.com',
                'telephone' => '+22790000000',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Formateur',
                'prenom' => 'Test',
                'email' => 'formateur@darasi.com',
                'telephone' => '+22790000001',
                'password' => Hash::make('password'),
                'role' => 'formateur',
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Apprenant',
                'prenom' => 'Test',
                'email' => 'apprenant@darasi.com',
                'telephone' => '+22790000002',
                'password' => Hash::make('password'),
                'role' => 'apprenant',
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Jean',
                'prenom' => 'Dupont',
                'email' => 'jean.dupont@test.com',
                'telephone' => '+22790000003',
                'password' => Hash::make('password'),
                'role' => 'apprenant',
                'is_active' => true,
                'email_verified_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Marie',
                'prenom' => 'Martin',
                'email' => 'marie.martin@test.com',
                'telephone' => '+22790000004',
                'password' => Hash::make('password'),
                'role' => 'apprenant',
                'is_active' => true,
                'email_verified_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']], // Condition : vérifier par email
                $user // Données à insérer ou mettre à jour
            );
        }
    }
}