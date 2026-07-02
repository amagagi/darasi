<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pole;
use App\Models\Categorie;
use App\Models\Niveau;
use App\Models\User;
use App\Models\Cours;
use App\Models\Module;
use App\Models\Lecon;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ======================
        // POLES
        // ======================
        $it = Pole::create([
            'nom' => 'IT',
            'description' => 'Informatique',
            'slug' => 'it',
            'ordre' => 1,
            'is_active' => 1,
        ]);

        $scolaire = Pole::create([
            'nom' => 'Scolaire',
            'description' => 'Formation scolaire',
            'slug' => 'scolaire',
            'ordre' => 2,
            'is_active' => 1,
        ]);

        $etudiant = Pole::create([
            'nom' => 'Étudiant',
            'description' => 'Formation universitaire',
            'slug' => 'etudiant',
            'ordre' => 3,
            'is_active' => 1,
        ]);

        // ======================
        // CATEGORIES
        // ======================
        $catProg = Categorie::create([
            'pole_id' => $it->id,
            'nom' => 'Programmation',
            'slug' => 'programmation',
            'ordre' => 1,
        ]);

        // ======================
        // NIVEAUX
        // ======================
        $debutant = Niveau::create([
            'pole_id' => $it->id,
            'libelle' => 'Débutant',
            'ordre' => 1,
        ]);

        // ======================
        // USER FORMATEUR
        // ======================
        $formateur = User::create([
            'nom' => 'Admin',
            'prenom' => 'Formateur',
            'email' => 'formateur@test.com',
            'telephone' => '90000000',
            'password' => Hash::make('password'),
            'role' => 'formateur',
        ]);

        // ======================
        // COURS
        // ======================
        $cours = Cours::create([
            'titre' => 'Laravel de A à Z',
            'description' => 'Cours complet Laravel',
            'objectifs' => 'Maîtriser Laravel',
            'prerequis' => 'PHP',
            'pole_id' => $it->id,
            'formateur_id' => $formateur->id,
            'categorie_id' => $catProg->id,
            'niveau_id' => $debutant->id,
            'prix' => 0,
            'est_gratuit' => 1,
            'statut' => 'publie',
            'note_moyenne' => 0,
            'nb_apprenants' => 0,
        ]);

        // ======================
        // MODULES
        // ======================
        $module1 = Module::create([
            'cours_id' => $cours->id,
            'titre' => 'Introduction Laravel',
            'description' => 'Bases du framework',
            'ordre' => 1,
        ]);

        $module2 = Module::create([
            'cours_id' => $cours->id,
            'titre' => 'CRUD Laravel',
            'description' => 'Créer un CRUD complet',
            'ordre' => 2,
        ]);

        // ======================
        // LEÇONS
        // ======================
        Lecon::create([
            'module_id' => $module1->id,
            'titre' => 'Installation Laravel',
            'contenu' => 'Comment installer Laravel',
            'type' => 'video',
            'ordre' => 1,
        ]);

        Lecon::create([
            'module_id' => $module1->id,
            'titre' => 'Structure projet',
            'contenu' => 'Organisation Laravel',
            'type' => 'texte',
            'ordre' => 2,
        ]);

        Lecon::create([
            'module_id' => $module2->id,
            'titre' => 'Créer un CRUD',
            'contenu' => 'CRUD complet step by step',
            'type' => 'video',
            'ordre' => 1,
        ]);
    }
}