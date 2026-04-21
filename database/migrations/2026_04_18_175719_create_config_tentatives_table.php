<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('config_tentatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('test_final_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('max_tentatives')->default(3);
            $table->integer('delai_heures')->default(24);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('config_tentatives');
    }
};