<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Signatures FIGÉES sur un certificat.
 *
 * Nom, fonction et image sont recopiés au moment de l'émission : changer de
 * directeur, ou remplacer sa signature, ne modifie jamais un certificat déjà
 * délivré. Les certificats existants n'ont aucune ligne : leurs signatures
 * sont figées au premier téléchargement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificat_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificat_id')->constrained('certificats')->cascadeOnDelete();
            // Simple trace du signataire d'origine, null s'il a été supprimé
            // depuis : ce qui est imprimé repose sur les colonnes ci-dessous.
            $table->foreignId('signataire_id')->nullable()->constrained('signataires')->nullOnDelete();
            $table->string('nom', 150);
            $table->string('fonction', 150);
            $table->string('image_signature', 500)->nullable();
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();

            $table->index(['certificat_id', 'ordre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificat_signatures');
    }
};
