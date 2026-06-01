<?php
// database/migrations/2026_06_01_xxxxxx_add_en_attente_to_abonnements_souscrits_statut.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute la valeur 'en_attente' à l'ENUM de la colonne statut dans abonnements_souscrits
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE abonnements_souscrits MODIFY COLUMN statut ENUM('actif', 'expire', 'annule', 'suspendu', 'en_attente') NOT NULL DEFAULT 'actif'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE abonnements_souscrits MODIFY COLUMN statut ENUM('actif', 'expire', 'annule', 'suspendu') NOT NULL DEFAULT 'actif'");
    }
};