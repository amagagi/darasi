<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cours', function (Blueprint $table) {
            $table->foreignId('formateur_id')->after('pole_id')->constrained('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('cours', function (Blueprint $table) {
            $table->dropForeign(['formateur_id']);
            $table->dropColumn('formateur_id');
        });
    }
};