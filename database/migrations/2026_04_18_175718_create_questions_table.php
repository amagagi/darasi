<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('test_final_id')->nullable()->constrained()->onDelete('cascade');
            $table->text('question');
            $table->enum('type', ['qcm', 'ouverte']);
            $table->integer('points')->default(1);
            $table->integer('ordre')->default(0);
            $table->timestamps();
            
            $table->index(['test_id', 'test_final_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};