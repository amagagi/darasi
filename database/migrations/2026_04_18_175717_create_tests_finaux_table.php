<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tests_finaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cours_id')->constrained()->onDelete('cascade');
            $table->string('titre', 200);
            $table->text('description')->nullable();
            $table->decimal('note_minimale', 5, 2)->default(70);
            $table->integer('duree_limite')->nullable();
            $table->timestamps();
            
            $table->unique('cours_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tests_finaux');
    }
};