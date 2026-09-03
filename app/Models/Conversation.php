<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    public const TYPE_GROUPE = 'groupe';
    public const TYPE_PRIVE = 'prive';

    protected $fillable = [
        'cours_id',
        'type',
        'apprenant_id',
        'dernier_message_le',
    ];

    protected $casts = [
        'dernier_message_le' => 'datetime',
    ];

    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    /** Interlocuteur d'un fil privé ; null pour un fil de groupe. */
    public function apprenant()
    {
        return $this->belongsTo(User::class, 'apprenant_id');
    }

    public function messages()
    {
        return $this->hasMany(ConversationMessage::class);
    }

    public function lectures()
    {
        return $this->hasMany(ConversationLecture::class);
    }

    public function estGroupe(): bool
    {
        return $this->type === self::TYPE_GROUPE;
    }

    /**
     * Récupère (ou crée) le fil de groupe d'un cours.
     */
    public static function pourCours(Cours $cours): self
    {
        return self::firstOrCreate([
            'cours_id' => $cours->id,
            'type' => self::TYPE_GROUPE,
            'apprenant_id' => null,
        ]);
    }

    /**
     * Récupère (ou crée) le fil privé entre le formateur du cours et un
     * apprenant donné.
     */
    public static function privee(Cours $cours, int $apprenantId): self
    {
        return self::firstOrCreate([
            'cours_id' => $cours->id,
            'type' => self::TYPE_PRIVE,
            'apprenant_id' => $apprenantId,
        ]);
    }

    /**
     * Qui peut voir et écrire dans ce fil.
     *
     * Les participants sont déduits des inscriptions, jamais stockés : un
     * apprenant désinscrit perd donc l'accès immédiatement, sans traitement
     * de synchronisation.
     */
    public function autorise(User $user): bool
    {
        $cours = $this->cours;

        if ($cours === null) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        $estFormateur = (int) $cours->formateur_id === (int) $user->id;

        if ($this->estGroupe()) {
            return $estFormateur || $this->estInscrit($user, $cours);
        }

        // Fil privé : uniquement le formateur du cours et l'apprenant concerné.
        return $estFormateur
            || ((int) $this->apprenant_id === (int) $user->id && $this->estInscrit($user, $cours));
    }

    private function estInscrit(User $user, Cours $cours): bool
    {
        return Inscription::where('apprenant_id', $user->id)
            ->where('cours_id', $cours->id)
            ->exists();
    }

    /**
     * Conversations visibles par un utilisateur.
     *
     * Formateur : tous les fils de ses cours. Apprenant : les fils de groupe de
     * ses cours, plus ses propres fils privés.
     */
    public function scopeVisiblesPar(Builder $query, User $user): Builder
    {
        if ($user->role === 'admin') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            // Fils des cours dont l'utilisateur est le formateur.
            $q->whereHas('cours', fn (Builder $c) => $c->where('formateur_id', $user->id));

            // Fils de groupe des cours auxquels il est inscrit.
            $q->orWhere(function (Builder $g) use ($user) {
                $g->where('type', self::TYPE_GROUPE)
                    ->whereIn('cours_id', Inscription::where('apprenant_id', $user->id)->select('cours_id'));
            });

            // Ses propres fils privés.
            $q->orWhere(function (Builder $p) use ($user) {
                $p->where('type', self::TYPE_PRIVE)
                    ->where('apprenant_id', $user->id);
            });
        });
    }

    /** Nombre de messages non lus pour cet utilisateur. */
    public function nonLus(User $user): int
    {
        $dernierLu = $this->lectures()
            ->where('user_id', $user->id)
            ->value('dernier_message_lu_id') ?? 0;

        return $this->messages()
            ->where('id', '>', $dernierLu)
            ->where('expediteur_id', '!=', $user->id)
            ->where('est_masque', false)
            ->count();
    }
}
