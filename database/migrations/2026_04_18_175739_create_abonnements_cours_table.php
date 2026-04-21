<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abonnements_cours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abonnement_type_id')->constrained('abonnements_types')->onDelete('cascade');
            $table->foreignId('cours_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['abonnement_type_id', 'cours_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abonnements_cours');
    }
};