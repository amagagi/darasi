<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tentatives_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscription_id')->constrained()->onDelete('cascade');
            $table->foreignId('test_id')->constrained()->onDelete('cascade');
            $table->decimal('note', 5, 2)->nullable();
            $table->boolean('est_valide')->default(false);
            $table->integer('tentative_numero')->default(1);
            $table->timestamp('date_tentative')->useCurrent();
            $table->timestamp('date_prochaine_autorisee')->nullable();
            $table->timestamps();
            
            $table->index(['inscription_id', 'test_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tentatives_tests');
    }
};