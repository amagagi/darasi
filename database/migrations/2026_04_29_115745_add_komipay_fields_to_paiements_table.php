<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            // Ajout des champs pour KomiPay
            $table->string('transaction_id', 191)->unique()->nullable()->after('reference_komipay');
            $table->integer('tentatives')->default(0)->after('transaction_id');
            $table->text('erreur_message')->nullable()->after('tentatives');
            $table->string('code_validation', 50)->nullable()->after('erreur_message');
        });
    }

    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropColumn([
                'transaction_id',
                'tentatives',
                'erreur_message',
                'code_validation'
            ]);
        });
    }
};