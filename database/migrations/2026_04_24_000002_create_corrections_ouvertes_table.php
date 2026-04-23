<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corrections_ouvertes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reponse_id')->constrained('reponses_questions')->onDelete('cascade');
            $table->decimal('note_accordee', 5, 2);
            $table->text('commentaire')->nullable();
            $table->foreignId('corrige_par')->constrained('users');
            $table->timestamp('date_correction')->useCurrent();
            $table->timestamps();
            
            $table->index('reponse_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corrections_ouvertes');
    }
};