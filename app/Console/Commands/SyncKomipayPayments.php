<?php
// app/Console/Commands/SyncKomipayPayments.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Paiement;
use App\Models\Inscription;
use App\Models\Notification;
use App\Services\KomiPayService;

class SyncKomipayPayments extends Command
{
    protected $signature = 'komipay:sync';
    protected $description = 'Synchronise les paiements en attente avec KomiPay';

    public function handle(KomiPayService $komiPayService)
    {
        $this->info('🔍 Synchronisation des paiements KomiPay...');

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
                    \DB::transaction(function () use ($paiement) {
                        $paiement->update([
                            'statut' => 'paye',
                            'date_paiement' => now()
                        ]);

                        Inscription::firstOrCreate(
                            [
                                'apprenant_id' => $paiement->apprenant_id,
                                'cours_id' => $paiement->cours_id
                            ],
                            [
                                'statut' => 'actif',
                                'progression' => 0,
                                'date_debut' => now()
                            ]
                        );

                        $this->info("✅ Paiement confirmé: {$paiement->reference_komipay}");
                    });
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