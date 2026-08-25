<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * L'enum mode_paiement ne contenait que AMANATA/MY_NITA/CARTE, mais le
 * validateur de l'API (PaiementController::initier) et le formulaire Filament
 * acceptent aussi AIRTEL_MONEY et CREDIT_CARD — un paiement initié avec l'un
 * de ces deux modes plantait avec une violation de contrainte en base au
 * moment de Paiement::create()/save().
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE paiements MODIFY mode_paiement ENUM('AMANATA', 'MY_NITA', 'CARTE', 'AIRTEL_MONEY', 'CREDIT_CARD') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE paiements MODIFY mode_paiement ENUM('AMANATA', 'MY_NITA', 'CARTE') NOT NULL");
    }
};
