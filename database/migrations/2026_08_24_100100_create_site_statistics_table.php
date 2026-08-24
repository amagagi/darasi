<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_statistics', function (Blueprint $table) {
            $table->id();

            $table->string('label');
            // Chaîne libre, pas un entier : supporte les suffixes ("1200+",
            // "98%") sans schéma séparé pour la mise en forme.
            $table->string('value');
            $table->string('icon')->nullable();

            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'display_order'], 'site_statistics_actives_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_statistics');
    }
};
