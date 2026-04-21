<?php
// app/Models/AbonnementType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbonnementType extends Model
{
    protected $table = 'abonnements_types';  // Ajoutez cette ligne
    protected $fillable = [
        'nom', 'description', 'duree_jours', 'prix',
        'nb_cours_max', 'est_populaire', 'est_actif', 'ordre'
    ];

    protected $casts = [
        'est_populaire' => 'boolean',
        'est_actif' => 'boolean',
    ];

    public function souscriptions()
    {
        return $this->hasMany(AbonnementSouscrit::class);
    }

    public function cours()
    {
        return $this->belongsToMany(Cours::class, 'abonnements_cours', 'abonnement_type_id', 'cours_id');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }
}