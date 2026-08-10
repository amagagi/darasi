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

            // Nouveaux seeders
            TestsTableSeeder::class,
            TestsFinauxTableSeeder::class,
            QuestionsTableSeeder::class,
            ChoixQuestionsTableSeeder::class,
            ContenusJuridiquesTableSeeder::class,
            ConfigTentativesTableSeeder::class,
            TentativesTestsTableSeeder::class,        // ← AJOUTER
            TentativesTestFinalTableSeeder::class,    // ← AJOUTER
            ReponsesQuestionsTableSeeder::class,      // ← AJOUTER
            CorrectionsOuvertesTableSeeder::class,    // ← AJOUTER
            AbonnementsSouscritsTableSeeder::class,   // ← AJOUTER

            // Contenus éditoriaux de la vitrine (vision, mission, valeurs)
            ContenusSiteTableSeeder::class,
        ]);
    }
}