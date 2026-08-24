<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_counters', function (Blueprint $table) {
            $table->id();

            // Une ligne par jour calendaire.
            $table->date('date')->unique();
            // Nombre de visites (sessions distinctes) de ce jour précis.
            $table->unsignedInteger('today_visits')->default(0);
            // Cumul total dénormalisé à la fin de ce jour : permet au footer et
            // au dashboard de lire le total sans jamais faire de COUNT(*) sur
            // la table site_visits, potentiellement volumineuse.
            $table->unsignedBigInteger('total_visits')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_counters');
    }
};
