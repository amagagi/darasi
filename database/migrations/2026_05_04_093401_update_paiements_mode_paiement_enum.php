<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Solution pour MySQL: modifier l'ENUM
        DB::statement("ALTER TABLE paiements MODIFY COLUMN mode_paiement ENUM('AMANATA', 'MY_NITA', 'CARTE', 'AIRTEL_MONEY', 'CREDIT_CARD') NOT NULL");
    }

    public function down(): void
    {
        // Revenir à l'ancien ENUM
        DB::statement("ALTER TABLE paiements MODIFY COLUMN mode_paiement ENUM('AMANATA', 'MY_NITA', 'CARTE', 'AIRTEL_MONEY') NOT NULL");
    }
};