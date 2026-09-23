<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Signataires propres à un cours.
 *
 * Facultatif : un cours sans ligne ici utilise les signataires par défaut.
 * Permet par exemple de faire signer un parcours partenaire par le responsable
 * du partenaire.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cours_signataire', function (Blueprint $table) {
            $table->foreignId('cours_id')->constrained('cours')->cascadeOnDelete();
            $table->foreignId('signataire_id')->constrained('signataires')->cascadeOnDelete();

            $table->primary(['cours_id', 'signataire_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cours_signataire');
    }
};
