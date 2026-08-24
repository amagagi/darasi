<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();

            $table->timestamp('visited_at');
            // Jamais l'IP en clair (RGPD) : sha256(ip . APP_KEY).
            $table->string('ip_hash', 64);
            $table->string('user_agent')->nullable();
            $table->string('page_url')->nullable();
            $table->string('session_id', 64)->nullable();

            $table->timestamps();

            // Requête chaude n°1 : anti-doublon (une visite par session/IP sur
            // une fenêtre de 30 min), exécutée à chaque appel du middleware.
            $table->index(['session_id', 'visited_at'], 'site_visits_dedup_index');
            // Requête chaude n°2 : agrégation quotidienne et top des pages.
            $table->index(['page_url', 'visited_at'], 'site_visits_top_pages_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};
