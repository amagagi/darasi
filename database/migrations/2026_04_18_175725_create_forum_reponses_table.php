<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_reponses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discussion_id')->constrained('forum_discussions')->onDelete('cascade');
            $table->foreignId('formateur_id')->constrained('users')->onDelete('cascade');
            $table->text('contenu');
            $table->timestamps();
            
            $table->index('discussion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_reponses');
    }
};