<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tentatives_test_final', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscription_id')->constrained()->onDelete('cascade');
            $table->foreignId('test_final_id')->constrained()->onDelete('cascade');
            $table->decimal('note', 5, 2)->nullable();
            $table->boolean('est_reussi')->default(false);
            $table->integer('tentative_numero')->default(1);
            $table->timestamp('date_tentative')->useCurrent();
            $table->timestamp('date_prochaine_autorisee')->nullable();
            $table->boolean('a_obtenu_certificat')->default(false);
            $table->timestamp('date_obtention_certificat')->nullable();
            $table->timestamps();
            
            $table->index(['inscription_id', 'test_final_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tentatives_test_final');
    }
};