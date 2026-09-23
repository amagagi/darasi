<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Signataires des certificats (directeur, responsable pédagogique...).
 *
 * L'image de signature est stockée sur le disque PRIVÉ : exposée publiquement,
 * elle pourrait être copiée et apposée sur un faux document.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signataires', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 150);
            $table->string('fonction', 150);
            $table->string('image_signature', 500)->nullable();
            $table->unsignedSmallInteger('ordre')->default(0);
            // Signataire « par défaut » : apposé sur les cours qui n'ont pas
            // de signataires propres (table cours_signataire).
            $table->boolean('est_actif')->default(true);
            $table->timestamps();

            $table->index(['est_actif', 'ordre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signataires');
    }
};
