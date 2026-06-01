<?php
// database/migrations/2026_06_01_xxxxxx_add_abonnement_type_to_notifications_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Ajoute la valeur 'abonnement' à l'ENUM de la colonne type dans la table notifications
     * Cela permet de distinguer les notifications liées aux abonnements
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('cours', 'quiz', 'paiement', 'forum', 'systeme', 'abonnement') NOT NULL DEFAULT 'systeme'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('cours', 'quiz', 'paiement', 'forum', 'systeme') NOT NULL DEFAULT 'systeme'");
    }
};