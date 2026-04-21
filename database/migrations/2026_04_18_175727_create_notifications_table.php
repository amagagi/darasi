<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('titre', 200);
            $table->text('message');
            $table->enum('type', ['cours', 'quiz', 'paiement', 'forum', 'systeme'])->default('systeme');
            $table->boolean('est_lu')->default(false);
            $table->json('data')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'est_lu', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};