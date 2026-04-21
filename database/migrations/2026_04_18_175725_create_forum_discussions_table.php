<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_discussions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cours_id')->constrained()->onDelete('cascade');
            $table->foreignId('apprenant_id')->constrained('users')->onDelete('cascade');
            $table->string('titre', 200);
            $table->text('contenu');
            $table->boolean('est_resolu')->default(false);
            $table->timestamps();
            
            $table->index(['cours_id', 'est_resolu']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_discussions');
    }
};