<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platforms', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();
            // ~160 caractères, pour la carte de la page liste. La longueur
            // n'est pas contrainte en base (comme ailleurs dans ce projet),
            // seulement au formulaire.
            $table->string('short_description');
            $table->text('description')->nullable();

            $table->string('logo_path')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->string('url');
            $table->string('category')->nullable();

            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'display_order'], 'platforms_actives_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platforms');
    }
};
