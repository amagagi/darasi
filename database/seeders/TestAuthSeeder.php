<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestAuthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'nom' => 'User',
                'prenom' => 'Apprenant',
                'email' => 'user@test.com',
                'telephone' => '+22700000003',
                'password' => Hash::make('password'),
                'role' => 'apprenant',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Student',
                'prenom' => 'Darasi',
                'email' => 'student@darasi.com',
                'telephone' => '+22700000004',
                'password' => Hash::make('password'),
                'role' => 'apprenant',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Teacher',
                'prenom' => 'Test',
                'email' => 'teacher@test.com',
                'telephone' => '+22700000005',
                'password' => Hash::make('password'),
                'role' => 'formateur',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Admin',
                'prenom' => 'Test',
                'email' => 'admin_test@test.com',
                'telephone' => '+22700000006',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
