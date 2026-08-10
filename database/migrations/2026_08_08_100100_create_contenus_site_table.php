<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contenus_site', function (Blueprint $table) {
            $table->id();

            // Identifiant stable référencé par le frontend (vision, mission, ...).
            // Un seul enregistrement par clé.
            $table->string('cle')->unique();

            $table->string('titre');
            $table->string('sous_titre')->nullable();
            $table->text('contenu');

            // Nom d'icône Material résolu côté Flutter (cf. _iconeDepuisNom).
            $table->string('icone')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->boolean('est_actif')->default(true);

            $table->foreignId('modifie_par')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contenus_site');
    }
};
