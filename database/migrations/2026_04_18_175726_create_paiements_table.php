<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apprenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('cours_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('abonnement_type_id')->nullable()->constrained('abonnements_types');
            $table->decimal('montant', 10, 0);
            $table->string('reference_komipay', 100)->nullable();
            $table->enum('statut', ['en_attente', 'paye', 'echoue', 'rembourse'])->default('en_attente');
            $table->enum('mode_paiement', ['AMANATA', 'MY_NITA', 'CARTE']);
            $table->timestamp('date_paiement')->nullable();
            $table->timestamps();
            
            $table->index(['statut', 'reference_komipay']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};