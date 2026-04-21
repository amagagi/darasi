<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cours', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 200);
            $table->text('description')->nullable();
            $table->text('objectifs')->nullable();
            $table->text('prerequis')->nullable();
            
            // Liens
            $table->foreignId('pole_id')->constrained();
            $table->foreignId('categorie_id')->nullable()->constrained();
            $table->foreignId('niveau_id')->nullable()->constrained();
            
            // Médias
            $table->string('image_couverture')->nullable();
            $table->string('video_presentation')->nullable();
            
            // Certification
            $table->boolean('est_certifiant')->default(false);
            $table->decimal('note_minimale_certificat', 5, 2)->default(70);
            
            // Prix
            $table->decimal('prix', 10, 0)->default(0);
            $table->boolean('est_gratuit')->default(false);
            
            // Statut
            $table->enum('statut', ['brouillon', 'publie', 'archive'])->default('brouillon');
            
            // Métriques
            $table->decimal('note_moyenne', 3, 2)->default(0);
            $table->integer('nb_apprenants')->default(0);
            
            $table->timestamps();
            $table->timestamp('published_at')->nullable();
            
            $table->index(['pole_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cours');
    }
};