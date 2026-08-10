<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Annonce extends Model
{
    protected $table = 'annonces';

    protected $fillable = [
        'titre',
        'extrait',
        'contenu',
        'type',
        'cible',
        'afficher_banniere',
        'afficher_actualites',
        'est_permanente',
        'lien_url',
        'lien_libelle',
        'image',
        'est_publiee',
        'publiee_le',
        'expire_le',
        'priorite',
        'cree_par',
    ];

    protected $casts = [
        'afficher_banniere' => 'boolean',
        'afficher_actualites' => 'boolean',
        'est_permanente' => 'boolean',
        'est_publiee' => 'boolean',
        'publiee_le' => 'datetime',
        'expire_le' => 'datetime',
        'priorite' => 'integer',
    ];

    public function creePar()
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    /**
     * Annonces réellement visibles maintenant : publiées, dont la date de
     * publication est passée et qui n'ont pas expiré.
     *
     * `publiee_le` nulle est traitée comme « immédiatement » afin qu'une annonce
     * publiée sans date planifiée reste visible.
     */
    public function scopeActive(Builder $query): Builder
    {
        $maintenant = now();

        return $query
            ->where('est_publiee', true)
            ->where(fn (Builder $q) => $q
                ->whereNull('publiee_le')
                ->orWhere('publiee_le', '<=', $maintenant))
            ->where(fn (Builder $q) => $q
                ->whereNull('expire_le')
                ->orWhere('expire_le', '>', $maintenant));
    }

    /**
     * Filtre sur l'audience. `tous` répond aussi bien à `public` qu'à
     * `connectes`.
     */
    public function scopePourCible(Builder $query, string $cible): Builder
    {
        return $query->whereIn('cible', [$cible, 'tous']);
    }

    /** Tri d'affichage : priorité décroissante, puis la plus récente. */
    public function scopeOrdreAffichage(Builder $query): Builder
    {
        return $query
            ->orderByDesc('priorite')
            ->orderByRaw('COALESCE(publiee_le, created_at) DESC');
    }
}
