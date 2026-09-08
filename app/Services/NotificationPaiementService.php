<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Paiement;

/**
 * Trace persistante du cycle de vie d'un paiement dans les notifications.
 *
 * Motivation : l'apprenant ne voyait l'état de son paiement que dans une boîte
 * de dialogue éphémère. Fermée ou manquée, l'information était perdue — or un
 * paiement Mobile Money se confirme parfois plusieurs minutes après, voire via
 * la synchronisation planifiée.
 *
 * Une SEULE notification par paiement, mise à jour au fil des états et remise
 * en « non lu » à chaque changement : la liste reste lisible, mais un passage
 * de « en attente » à « confirmé » refait remonter l'information.
 */
class NotificationPaiementService
{
    /** Paiement initié, en attente de validation. */
    public function enAttente(Paiement $paiement, ?string $codeAchat = null): void
    {
        $intitule = $this->intituleObjet($paiement);

        $message = "Votre paiement de {$this->montant($paiement)} pour {$intitule} est en attente de validation.";

        if (filled($codeAchat)) {
            // Le code d'achat MyNITA permet un règlement en guichet : c'est
            // précisément l'information qu'il ne faut pas perdre.
            $message .= " Code d'achat : {$codeAchat}";
        }

        $this->enregistrer($paiement, 'Paiement en attente', $message, [
            'statut' => 'en_attente',
            'code_achat' => $codeAchat,
        ]);
    }

    /** Paiement confirmé : l'accès est ouvert. */
    public function confirme(Paiement $paiement): void
    {
        $intitule = $this->intituleObjet($paiement);

        $this->enregistrer(
            $paiement,
            'Paiement confirmé',
            "Votre paiement de {$this->montant($paiement)} pour {$intitule} a été confirmé. Vous y avez désormais accès.",
            ['statut' => 'paye'],
        );
    }

    /** Paiement échoué ou expiré. */
    public function echoue(Paiement $paiement, ?string $raison = null): void
    {
        $intitule = $this->intituleObjet($paiement);

        $message = "Votre paiement de {$this->montant($paiement)} pour {$intitule} n'a pas abouti.";
        $message .= $raison !== null
            ? " {$raison}"
            : ' Aucun montant n\'a été débité. Vous pouvez réessayer.';

        $this->enregistrer($paiement, 'Paiement non abouti', $message, [
            'statut' => 'echoue',
        ]);
    }

    /**
     * Crée la notification du paiement, ou met à jour celle qui existe.
     *
     * L'unicité repose sur `data->transaction_id` : pas de colonne dédiée à
     * ajouter, et la recherche reste exacte.
     */
    private function enregistrer(Paiement $paiement, string $titre, string $message, array $donnees): void
    {
        $donnees = array_merge($donnees, [
            'transaction_id' => $paiement->transaction_id,
            'paiement_id' => $paiement->id,
            'cours_id' => $paiement->cours_id,
        ]);

        $existante = Notification::query()
            ->where('user_id', $paiement->apprenant_id)
            ->where('type', 'paiement')
            ->whereJsonContains('data->transaction_id', $paiement->transaction_id)
            ->first();

        if ($existante !== null) {
            $existante->update([
                'titre' => $titre,
                'message' => $message,
                'data' => $donnees,
                // Repasse en non lu : le changement d'état doit se voir.
                'est_lu' => false,
            ]);

            return;
        }

        Notification::create([
            'user_id' => $paiement->apprenant_id,
            'titre' => $titre,
            'message' => $message,
            'type' => 'paiement',
            'data' => $donnees,
            'est_lu' => false,
        ]);
    }

    private function montant(Paiement $paiement): string
    {
        return number_format((float) $paiement->montant, 0, ',', ' ') . ' FCFA';
    }

    /** Libellé de ce qui est acheté : un cours, ou un abonnement. */
    private function intituleObjet(Paiement $paiement): string
    {
        if ($paiement->cours_id !== null) {
            return 'le cours « ' . ($paiement->cours?->titre ?? 'sélectionné') . ' »';
        }

        if ($paiement->abonnement_type_id !== null) {
            return 'votre abonnement';
        }

        return 'votre achat';
    }
}
