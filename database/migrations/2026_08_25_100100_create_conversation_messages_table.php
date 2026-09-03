<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('expediteur_id')->constrained('users')->cascadeOnDelete();

            $table->text('contenu');

            // Modération : masque le message sans le supprimer, pour conserver
            // la trace côté back-office.
            $table->boolean('est_masque')->default(false);
            $table->foreignId('masque_par')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Requête chaude : les messages d'un fil postérieurs à un id donné
            // (récupération incrémentale via ?since=).
            $table->index(['conversation_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_messages');
    }
};
