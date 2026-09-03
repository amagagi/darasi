<?php
// app/Models/Lecon.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Lecon extends Model
{
    /**
     * Disque privé des médias de cours (storage/app/private).
     *
     * Les PDF et vidéos ne sont JAMAIS servis depuis le disque `public` : ils
     * seraient alors téléchargeables par simple devinette d'URL, y compris pour
     * les cours payants. L'accès passe par une URL signée temporaire, cf.
     * App\Http\Controllers\Api\LeconMediaController.
     */
    public const DISQUE_PRIVE = 'local';

    /** Durée de validité d'une URL de lecture. */
    public const MINUTES_VALIDITE_URL = 30;

    protected $fillable = [
        'module_id', 'titre', 'type_contenu',
        'contenu_text', 'url_video', 'url_pdf', 'duree_video', 'ordre'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function progres()
    {
        return $this->hasMany(ProgresLecon::class);
    }

    /**
     * Valeur brute stockée pour le type demandé (`pdf` ou `video`).
     */
    public function valeurMediaBrute(string $type): ?string
    {
        $valeur = $type === 'pdf' ? $this->url_pdf : $this->url_video;

        return filled($valeur) ? trim($valeur) : null;
    }

    /**
     * Vrai si le média est hébergé chez nous (par opposition à un lien externe
     * type YouTube, que l'on ne peut évidemment pas servir nous-mêmes).
     */
    public function estMediaHeberge(string $type): bool
    {
        $valeur = $this->valeurMediaBrute($type);

        return $valeur !== null && ! Str::startsWith($valeur, ['http://', 'https://', '//']);
    }

    /**
     * Chemin relatif sur le disque privé.
     *
     * La base mélange trois formats hérités : `/storage/cours/x.pdf` (données
     * seedées, préfixe inclus), `lecons/pdfs/x.pdf` (téléversement Filament) et
     * les URL externes. On ramène tout à un chemin relatif exploitable.
     */
    public function cheminMediaRelatif(string $type): ?string
    {
        if (! $this->estMediaHeberge($type)) {
            return null;
        }

        $chemin = ltrim($this->valeurMediaBrute($type), '/');

        // Retire un éventuel préfixe `storage/` laissé par les anciennes données.
        if (Str::startsWith($chemin, 'storage/')) {
            $chemin = Str::after($chemin, 'storage/');
        }

        return $chemin === '' ? null : $chemin;
    }

    /** Le fichier est-il réellement présent sur le disque privé ? */
    public function mediaExiste(string $type): bool
    {
        $chemin = $this->cheminMediaRelatif($type);

        return $chemin !== null
            && Storage::disk(self::DISQUE_PRIVE)->exists($chemin);
    }

    /**
     * URL signée temporaire de lecture, à donner au lecteur PDF ou vidéo.
     *
     * Signée plutôt que protégée par jeton Sanctum : sur le Web, la balise
     * <video> et le moteur PDF chargent l'URL eux-mêmes et ne savent pas
     * ajouter d'en-tête Authorization.
     */
    public function urlMediaSignee(string $type): ?string
    {
        if (! $this->estMediaHeberge($type)) {
            // Lien externe (YouTube...) : renvoyé tel quel, rien à signer.
            return $this->valeurMediaBrute($type);
        }

        if ($this->cheminMediaRelatif($type) === null) {
            return null;
        }

        return URL::temporarySignedRoute(
            'lecons.media.stream',
            now()->addMinutes(self::MINUTES_VALIDITE_URL),
            ['lecon' => $this->id, 'type' => $type],
        );
    }

    /**
     * @deprecated Exposait le média en accès public direct. Utiliser
     *             urlMediaSignee() : conservé uniquement pour ne pas casser
     *             un appel résiduel.
     */
    public function getUrlAttribute()
    {
        return match ($this->type_contenu) {
            'video', 'pdf' => $this->urlMediaSignee($this->type_contenu),
            default => null,
        };
    }
}
