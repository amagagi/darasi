<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paiement_id')->constrained()->onDelete('cascade');
            $table->string('event_type', 50);
            $table->json('payload')->nullable();
            $table->timestamps();
            
            $table->index('paiement_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements_logs');
    }
};