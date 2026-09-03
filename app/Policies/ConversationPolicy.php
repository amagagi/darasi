<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

/**
 * Autorisations des fils de discussion.
 *
 * Toute la logique d'appartenance vit dans Conversation::autorise(), qui la
 * déduit des inscriptions au cours : un apprenant désinscrit perd l'accès
 * immédiatement, sans traitement de synchronisation.
 */
class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->autorise($user);
    }

    /** Écrire suppose les mêmes droits que lire. */
    public function envoyerMessage(User $user, Conversation $conversation): bool
    {
        return $conversation->autorise($user);
    }

    /**
     * Ouvrir un fil privé avec un apprenant donné : réservé au formateur du
     * cours (et aux administrateurs). Un apprenant ne peut pas ouvrir un fil
     * privé avec un autre apprenant.
     */
    public function ouvrirPrivee(User $user, Conversation $conversation): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return (int) $conversation->cours?->formateur_id === (int) $user->id;
    }

    /** Masquer un message : formateur du cours ou administrateur. */
    public function moderer(User $user, Conversation $conversation): bool
    {
        return $this->ouvrirPrivee($user, $conversation);
    }
}
