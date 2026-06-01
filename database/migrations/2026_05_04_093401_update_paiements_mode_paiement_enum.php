<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE paiements DROP CONSTRAINT IF EXISTS paiements_mode_paiement_check');
            DB::statement("ALTER TABLE paiements ADD CONSTRAINT paiements_mode_paiement_check CHECK (mode_paiement IN ('AMANATA', 'MY_NITA', 'CARTE', 'AIRTEL_MONEY', 'CREDIT_CARD'))");

            return;
        }

        DB::statement("ALTER TABLE paiements MODIFY COLUMN mode_paiement ENUM('AMANATA', 'MY_NITA', 'CARTE', 'AIRTEL_MONEY', 'CREDIT_CARD') NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE paiements DROP CONSTRAINT IF EXISTS paiements_mode_paiement_check');
            DB::statement("ALTER TABLE paiements ADD CONSTRAINT paiements_mode_paiement_check CHECK (mode_paiement IN ('AMANATA', 'MY_NITA', 'CARTE', 'AIRTEL_MONEY'))");

            return;
        }

        DB::statement("ALTER TABLE paiements MODIFY COLUMN mode_paiement ENUM('AMANATA', 'MY_NITA', 'CARTE', 'AIRTEL_MONEY') NOT NULL");
    }
};
