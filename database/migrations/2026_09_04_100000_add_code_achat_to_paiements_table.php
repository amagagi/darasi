<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Code d'achat renvoyé par KomiPay pour les paiements MyNITA.
     *
     * Constaté en production : la réponse d'initiation contient un champ
     * `code_achat` (ex. ACHATFDABC2B154F23) et le message invite à valider
     * « sur votre compte MyNITA […] ou dans un guichet NITA via le code
     * d'achat ». Ce code n'était ni stocké ni transmis au client : un apprenant
     * sans l'application MyNITA n'avait donc aucun moyen de payer au guichet.
     */
    public function up(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->string('code_achat', 100)->nullable()->after('reference_komipay');
        });
    }

    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropColumn('code_achat');
        });
    }
};
