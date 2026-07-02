<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vérifier si la table existe déjà avant de la créer
        if (!Schema::hasTable('contenus_juridiques')) {
            Schema::create('contenus_juridiques', function (Blueprint $table) {
                $table->id();
                $table->string('type', 50)->unique();
                $table->string('titre', 255);
                $table->longText('contenu');
                $table->boolean('est_actif')->default(true);
                $table->foreignId('modifie_par')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('date_modification')->useCurrent()->useCurrentOnUpdate();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Supprimer la table seulement si elle existe
        if (Schema::hasTable('contenus_juridiques')) {
            Schema::dropIfExists('contenus_juridiques');
        }
    }
};