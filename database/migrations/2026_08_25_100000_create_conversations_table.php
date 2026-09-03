<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fils de discussion entre un formateur et ses apprenants.
     *
     * Deux formes :
     *   - `groupe` : un seul fil par cours, ouvert à tous ses inscrits ;
     *     `apprenant_id` est nul.
     *   - `prive`  : un fil par couple (cours, apprenant).
     *
     * Il n'existe volontairement PAS de table de participants : ils se
     * déduisent des inscriptions au cours. Une table figée devrait être
     * resynchronisée à chaque inscription ou désinscription, ce qui finit
     * toujours par diverger.
     */
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cours_id')->constrained('cours')->cascadeOnDelete();
            $table->enum('type', ['groupe', 'prive'])->default('groupe');

            // Renseigné uniquement pour les conversations privées.
            $table->foreignId('apprenant_id')->nullable()
                ->constrained('users')->cascadeOnDelete();

            // Dénormalisé : permet de trier la liste des conversations sans
            // agréger la table des messages à chaque affichage.
            $table->timestamp('dernier_message_le')->nullable();

            $table->timestamps();

            // Un seul fil de groupe par cours, un seul fil privé par apprenant.
            // MySQL considère les NULL comme distincts : la contrainte ne gêne
            // donc pas les lignes de groupe, protégées par l'index partiel
            // applicatif (cf. Conversation::pourCours).
            $table->unique(['cours_id', 'type', 'apprenant_id'], 'conversations_unicite');
            $table->index(['cours_id', 'dernier_message_le']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
