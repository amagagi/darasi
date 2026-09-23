<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Personne dont la signature est apposée sur les certificats.
 */
class Signataire extends Model
{
    /**
     * Disque privé : une signature servie publiquement pourrait être copiée
     * sur un faux certificat.
     */
    public const DISQUE = 'local';

    /** Au-delà, les blocs de signature ne tiennent plus sur une ligne du A4. */
    public const MAXIMUM_PAR_CERTIFICAT = 3;

    protected $table = 'signataires';

    protected $fillable = [
        'nom',
        'fonction',
        'image_signature',
        'ordre',
        'est_actif',
    ];

    protected $casts = [
        'est_actif' => 'boolean',
        'ordre' => 'integer',
    ];

    /** Signataires par défaut, dans l'ordre d'impression. */
    public function scopeActifs(Builder $query): Builder
    {
        return $query->where('est_actif', true)->orderBy('ordre')->orderBy('id');
    }

    public function cours(): BelongsToMany
    {
        return $this->belongsToMany(Cours::class, 'cours_signataire');
    }
}
