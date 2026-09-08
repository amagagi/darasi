<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteStatistic extends Model
{
    use HasFactory;

    /**
     * Sources calculees a partir des donnees reelles.
     *
     * Quand `value_source` vaut l'une de ces cles, la valeur affichee est
     * calculee et la saisie manuelle est ignoree : impossible d'annoncer un
     * chiffre sans fondement.
     */
    public const SOURCE_APPRENANTS_INSCRITS = 'apprenants_inscrits';
    public const SOURCE_APPRENANTS_FORMES = 'apprenants_formes';
    public const SOURCE_VISITEURS = 'visiteurs';
    public const SOURCE_COURS_PUBLIES = 'cours_publies';
    public const SOURCE_CERTIFICATS = 'certificats_delivres';

    /** Libelles proposes dans le back-office. */
    public const SOURCES = [
        self::SOURCE_APPRENANTS_INSCRITS => 'Apprenants inscrits (calcule)',
        self::SOURCE_APPRENANTS_FORMES => 'Apprenants formes — parcours termine (calcule)',
        self::SOURCE_VISITEURS => 'Visiteurs du site (calcule)',
        self::SOURCE_COURS_PUBLIES => 'Cours publies (calcule)',
        self::SOURCE_CERTIFICATS => 'Certificats delivres (calcule)',
    ];

    protected $fillable = [
        'label',
        'value',
        'value_source',
        'icon',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('id');
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site-statistics.active'));
        static::deleted(fn () => Cache::forget('site-statistics.active'));
    }
}
