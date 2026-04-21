<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apprenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('cours_id')->constrained()->onDelete('cascade');
            $table->decimal('progression', 5, 2)->default(0);
            $table->boolean('tests_modules_valides')->default(false);
            $table->timestamp('date_debut')->useCurrent();
            $table->timestamp('date_completion')->nullable();
            $table->enum('statut', ['actif', 'suspendu', 'termine'])->default('actif');
            
            // Abonnement
            $table->foreignId('abonnement_id')->nullable()->constrained('abonnements_souscrits');
            $table->boolean('est_via_abonnement')->default(false);
            
            $table->timestamps();
            
            $table->unique(['apprenant_id', 'cours_id']);
            $table->index(['apprenant_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscriptions');
    }
};