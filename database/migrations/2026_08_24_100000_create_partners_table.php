<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            $table->string('logo_path');
            $table->string('website_url')->nullable();
            $table->text('description')->nullable();

            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Index de la requête chaude : les partenaires actifs, dans l'ordre
            // d'affichage.
            $table->index(['is_active', 'display_order'], 'partners_actifs_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
