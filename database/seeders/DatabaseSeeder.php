<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PolesTableSeeder::class,
            NiveauxTableSeeder::class,
            AbonnementsTypesTableSeeder::class,
            UsersTableSeeder::class,
            CategoriesTableSeeder::class,
            CoursTableSeeder::class,
            ModulesTableSeeder::class,
            LeconsTableSeeder::class,
            InscriptionsTableSeeder::class,
            DemandesFormationTableSeeder::class,
        ]);
    }
}