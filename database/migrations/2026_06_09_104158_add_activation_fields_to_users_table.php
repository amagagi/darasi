<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Pour désactiver n'importe quel compte
            $table->boolean('is_active')->default(true)->after('role');
            $table->timestamp('deactivated_at')->nullable()->after('is_active');
            $table->text('deactivated_reason')->nullable()->after('deactivated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'deactivated_at', 'deactivated_reason']);
        });
    }
};