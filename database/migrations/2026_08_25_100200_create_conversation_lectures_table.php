<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Curseur de lecture par utilisateur et par conversation.
     *
     * On mémorise l'id du dernier message lu plutôt qu'un drapeau par message :
     * un fil de groupe de 40 apprenants créerait sinon 40 lignes d'état à
     * chaque message envoyé. Le compteur de non-lus devient un simple
     * `count(id > dernier_message_lu_id)`.
     */
    public function up(): void
    {
        Schema::create('conversation_lectures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('dernier_message_lu_id')->default(0);

            $table->timestamps();

            $table->unique(['conversation_id', 'user_id'], 'conversation_lectures_unicite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_lectures');
    }
};
