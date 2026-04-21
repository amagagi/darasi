<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autorisations_correction', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formateur_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('cours_id')->constrained()->onDelete('cascade');
            $table->foreignId('autorise_par')->constrained('users');
            $table->timestamp('date_autorisation')->useCurrent();
            $table->boolean('est_active')->default(true);
            $table->timestamps();
            
            $table->unique(['formateur_id', 'cours_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autorisations_correction');
    }
};