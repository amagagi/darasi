<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abonnements_souscrits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apprenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('type_abonnement_id')->constrained('abonnements_types');
            $table->timestamp('date_debut');
            $table->timestamp('date_fin');
            $table->enum('statut', ['actif', 'expire', 'annule', 'suspendu'])->default('actif');
            $table->foreignId('paiement_id')->nullable()->constrained();
            $table->timestamps();
            
            $table->index(['apprenant_id', 'statut', 'date_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abonnements_souscrits');
    }
};