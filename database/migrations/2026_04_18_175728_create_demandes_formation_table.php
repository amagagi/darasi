<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_formation', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->string('email', 150);
            $table->string('telephone', 20)->nullable();
            $table->string('titre_cours_souhaite', 200);
            $table->text('description')->nullable();
            $table->string('domaine', 100)->nullable();
            $table->string('niveau_souhaite', 100)->nullable();
            $table->enum('statut', ['en_attente', 'prise_en_compte', 'realise', 'rejete'])->default('en_attente');
            $table->timestamp('traite_le')->nullable();
            $table->foreignId('traite_par')->nullable()->constrained('users');
            $table->text('commentaire_admin')->nullable();
            $table->timestamps();
            
            $table->index(['statut', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_formation');
    }
};