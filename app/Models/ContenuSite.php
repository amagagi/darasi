<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Blocs de contenu éditoriaux de la vitrine (vision, mission, valeurs...),
 * modifiables depuis le back-office sans redéploiement du frontend.
 */
class ContenuSite extends Model
{
    protected $table = 'contenus_site';

    /** Clés attendues par la section « Vision & Mission » de la landing page. */
    public const CLE_VISION = 'vision';
    public const CLE_MISSION = 'mission';
    public const CLE_VALEURS = 'valeurs';

    /**
     * Réglage du compteur de visites du footer (App\Http\Controllers\Api\
     * SiteVisitController::summary()) : `est_actif` pilote l'affichage,
     * `contenu` est le texte affiché (le jeton {n} est remplacé par le total).
     * Réutilise cette table plutôt qu'une table de réglages dédiée.
     */
    public const CLE_COMPTEUR_VISITES = 'compteur_visites';

    protected $fillable = [
        'cle',
        'titre',
        'sous_titre',
        'contenu',
        'icone',
        'ordre',
        'est_actif',
        'modifie_par',
    ];

    protected $casts = [
        'est_actif' => 'boolean',
        'ordre' => 'integer',
    ];

    public function modifiePar()
    {
        return $this->belongsTo(User::class, 'modifie_par');
    }

    public function scopeActif(Builder $query): Builder
    {
        return $query->where('est_actif', true);
    }
}
