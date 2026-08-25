<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * abonnements_types.categorie_id est nullable et le formulaire Filament
 * documente explicitement "Laisser vide pour un abonnement général (toutes
 * catégories)" — mais abonnements_souscrits.categorie_id était resté NOT
 * NULL, donc toute souscription à un abonnement général plantait avec une
 * violation de contrainte au moment de l'insertion. Les vérifications
 * d'accès (User::aAbonnementActifPourCategorie, AbonnementSouscrit::
 * peutAccederCours) sont mises à jour dans le même lot pour traiter
 * categorie_id = null comme "couvre toutes les catégories".
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE abonnements_souscrits MODIFY categorie_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE abonnements_souscrits MODIFY categorie_id BIGINT UNSIGNED NOT NULL');
    }
};
