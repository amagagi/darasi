<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contenus_juridiques', function (Blueprint $table) {
            // Ajouter les colonnes seulement si elles n'existent pas
            if (!Schema::hasColumn('contenus_juridiques', 'est_actif')) {
                $table->boolean('est_actif')->default(true);
            }
            
            if (!Schema::hasColumn('contenus_juridiques', 'modifie_par')) {
                $table->foreignId('modifie_par')->nullable()->constrained('users')->nullOnDelete();
            }
            
            if (!Schema::hasColumn('contenus_juridiques', 'date_modification')) {
                $table->timestamp('date_modification')->useCurrent()->useCurrentOnUpdate();
            }
            
            if (!Schema::hasColumn('contenus_juridiques', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('contenus_juridiques', function (Blueprint $table) {
            $table->dropColumn(['est_actif', 'modifie_par', 'date_modification']);
        });
    }
};