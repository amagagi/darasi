<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progres_lecons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscription_id')->constrained()->onDelete('cascade');
            $table->foreignId('lecon_id')->constrained()->onDelete('cascade');
            $table->boolean('est_complete')->default(false);
            $table->integer('temps_passe')->default(0);
            $table->timestamp('date_completion')->nullable();
            $table->timestamps();
            
            $table->unique(['inscription_id', 'lecon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progres_lecons');
    }
};