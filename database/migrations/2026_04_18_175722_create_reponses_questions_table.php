<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reponses_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tentative_test_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('tentative_final_id')->nullable()->constrained('tentatives_test_final')->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->text('reponse_texte')->nullable();
            $table->foreignId('choix_id')->nullable()->constrained('choix_questions');
            $table->boolean('est_correcte')->default(false);
            $table->decimal('points_obtenus', 5, 2)->default(0);
            $table->timestamps();
            
            $table->index(['tentative_test_id', 'tentative_final_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reponses_questions');
    }
};