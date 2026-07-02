<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vérifier si la colonne existe déjà avant de l'ajouter
        if (!Schema::hasColumn('tests', 'duree_limite')) {
            Schema::table('tests', function (Blueprint $table) {
                $table->integer('duree_limite')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tests', 'duree_limite')) {
            Schema::table('tests', function (Blueprint $table) {
                $table->dropColumn('duree_limite');
            });
        }
    }
};