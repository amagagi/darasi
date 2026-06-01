<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abonnements_types', function (Blueprint $table) {
            $table->foreignId('categorie_id')
                ->after('id')
                ->nullable()
                ->constrained('categories')
                ->onDelete('set null');
            
            $table->index(['categorie_id', 'est_actif'], 'idx_abonnements_types_cat_active');
        });
    }

    public function down(): void
    {
        Schema::table('abonnements_types', function (Blueprint $table) {
            $table->dropForeign(['categorie_id']);
            $table->dropColumn('categorie_id');
            $table->dropIndex('idx_abonnements_types_cat_active');
        });
    }
};