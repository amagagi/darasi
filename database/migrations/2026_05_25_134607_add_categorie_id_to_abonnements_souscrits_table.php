<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abonnements_souscrits', function (Blueprint $table) {
            $table->foreignId('categorie_id')
                ->after('type_abonnement_id')
                ->constrained('categories')
                ->onDelete('cascade');
            
            $table->index(['apprenant_id', 'categorie_id', 'statut', 'date_fin'], 'idx_abo_user_cat_status_date');
        });
    }

    public function down(): void
    {
        Schema::table('abonnements_souscrits', function (Blueprint $table) {
            $table->dropForeign(['categorie_id']);
            $table->dropColumn('categorie_id');
            $table->dropIndex('idx_abo_user_cat_status_date');
        });
    }
};