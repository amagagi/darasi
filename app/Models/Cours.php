<?php
// app/Models/Cours.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cours extends Model
{
    protected $fillable = [
        'titre', 'description', 'objectifs', 'prerequis',
        'pole_id', 'formateur_id', 'categorie_id', 'niveau_id',  // AJOUTE formateur_id
        'image_couverture', 'video_presentation',
        'est_certifiant', 'note_minimale_certificat',
        'prix', 'est_gratuit', 'statut',
        'note_moyenne', 'nb_apprenants', 'published_at'
    ];

    protected $casts = [
        'est_certifiant' => 'boolean',
        'est_gratuit' => 'boolean',
        'published_at' => 'datetime',
    ];

    // Relations
    public function pole()
    {
        return $this->belongsTo(Pole::class);
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('ordre');
    }

    public function testFinal()
    {
        return $this->hasOne(TestFinal::class);
    }

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    public function discussions()
    {
        return $this->hasMany(ForumDiscussion::class);
    }

    public function autorisationsCorrection()
    {
        return $this->hasMany(AutorisationCorrection::class);
    }

    // Accesseurs
    public function getPrixFormatteAttribute()
    {
        return number_format($this->prix, 0, ',', ' ') . ' FCFA';
    }

    public function isGratuit(): bool
    {
        return $this->prix == 0;
    }
    // Scope
    public function scopePublie($query)
    {
        return $query->where('statut', 'publie');
    }

    public function scopeGratuit($query)
    {
        return $query->where('est_gratuit', true)->orWhere('prix', 0);
    }

    public function scopePayant($query)
    {
        return $query->where('est_gratuit', false)->where('prix', '>', 0);
    }

    public function formateur()
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

            /**
         * Accesseur pour nb_apprenants (calculé dynamiquement)
         */
        public function getNbApprenantsAttribute(): int
        {
            return $this->inscriptions()->where('statut', 'actif')->count();
        }

        /**
         * Accesseur pour note_moyenne (calculée dynamiquement)
         */
        public function getNoteMoyenneAttribute(): float
        {
            try {
                $notes = \App\Models\TentativeTestFinal::whereHas('inscription', function ($query) {
                        $query->where('cours_id', $this->id)
                            ->where('statut', 'actif');
                    })
                    ->where('est_reussi', true)
                    ->pluck('note');

                if ($notes->isEmpty()) {
                    return 0.00;
                }

                return round($notes->avg(), 2);
            } catch (\Exception $e) {
                // En cas d'erreur (table inexistante par exemple)
                return 0.00;
            }
        }
        /**
         * Mutateur pour forcer la mise à jour quand on modifie le champ
         * (Optionnel: pour garder la BDD synchro avec l'accesseur)
         */
        public function updateStats(): void
        {
            $this->updateQuietly([
                'nb_apprenants' => $this->nb_apprenants,
                'note_moyenne' => $this->note_moyenne,
            ]);
        }
    
}