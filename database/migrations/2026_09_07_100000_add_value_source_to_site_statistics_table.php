<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Origine calculée d'un chiffre clé.
     *
     * Jusqu'ici, `value` était saisie à la main : rien n'empêchait d'afficher
     * « 15 000 apprenants » sans le moindre apprenant en base. Quand
     * `value_source` est renseignée, la valeur est calculée à partir des
     * données réelles et la saisie manuelle est ignorée.
     */
    public function up(): void
    {
        Schema::table('site_statistics', function (Blueprint $table) {
            $table->string('value_source', 40)->nullable()->after('value');
        });
    }

    public function down(): void
    {
        Schema::table('site_statistics', function (Blueprint $table) {
            $table->dropColumn('value_source');
        });
    }
};
