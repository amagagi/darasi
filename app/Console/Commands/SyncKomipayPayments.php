<?php
// app/Console/Commands/SyncKomipayPayments.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Paiement;
use App\Models\Notification;
use App\Services\KomiPayService;

class SyncKomipayPayments extends Command
{
    protected $signature = 'komipay:sync';
    protected $description = 'Synchronise les paiements en attente avec KomiPay';

    /**
     * Délai au-delà duquel une tentative sans référence est abandonnée.
     * Aligné sur la fenêtre de validation KomiPay (5 min), marge comprise.
     */
    private const MINUTES_ABANDON = 10;

    public function handle(KomiPayService $komiPayService)
    {
        $this->info('🔍 Synchronisation des paiements KomiPay...');

        // Tentatives n'ayant jamais obtenu de référence KomiPay : l'appel a
        // échoué avant d'aboutir (jeton, réseau, validation). Elles ne seront
        // jamais confirmées et restaient « en_attente » indéfiniment, ce qui
        // interdisait à l'apprenant toute nouvelle tentative sur ce cours.
        $abandonnees = Paiement::where('statut', 'en_attente')
            ->whereNull('reference_komipay')
            ->where('created_at', '<', now()->subMinutes(self::MINUTES_ABANDON))
            ->update(['statut' => 'echoue']);

        if ($abandonnees > 0) {
            $this->warn("🧹 {$abandonnees} tentative(s) sans référence abandonnée(s)");
        }

        $paiements = Paiement::where('statut', 'en_attente')
            ->whereNotNull('reference_komipay')
            ->get();

        if ($paiements->isEmpty()) {
            $this->info('✅ Aucun paiement à synchroniser');
            return;
        }

        $this->info("📊 {$paiements->count()} paiement(s) à vérifier");

        foreach ($paiements as $paiement) {
            $this->line("🔍 Vérification: {$paiement->reference_komipay}");

            try {
                $statut = $komiPayService->checkTransactionStatus($paiement->reference_komipay);

                if ($statut === 'success') {
                    // Gère aussi bien l'inscription à un cours que
                    // l'activation d'un abonnement, selon ce qui est
                    // renseigné sur le paiement.
                    $komiPayService->finaliserPaiement($paiement);
                    $this->info("✅ Paiement confirmé: {$paiement->reference_komipay}");
                } elseif ($statut === 'failed') {
                    $paiement->update(['statut' => 'echoue']);
                    $this->warn("❌ Paiement échoué: {$paiement->reference_komipay}");
                } else {
                    $this->line("⏳ Toujours en attente: {$paiement->reference_komipay}");
                }

            } catch (\Exception $e) {
                $this->error("Erreur: {$e->getMessage()}");
            }
        }

        $this->info('✅ Synchronisation terminée');
    }
}