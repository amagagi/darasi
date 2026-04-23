<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscription_id')->constrained()->onDelete('cascade');
            $table->foreignId('tentative_final_id')->constrained('tentatives_test_final')->onDelete('cascade');
            $table->string('code_verification', 50)->unique();
            $table->string('url_pdf', 500)->nullable();
            $table->timestamp('date_emission')->useCurrent();
            $table->boolean('est_valide')->default(true);
            $table->timestamp('date_revocation')->nullable();
            $table->foreignId('revoque_par')->nullable()->constrained('users');
            $table->text('motif_revocation')->nullable();
            $table->timestamps();
            
            $table->index(['code_verification', 'est_valide']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificats');
    }
};