<?php
// app/Models/AbonnementSouscrit.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AbonnementSouscrit extends Model
{
    protected $fillable = [
        'apprenant_id', 'type_abonnement_id', 'date_debut',
        'date_fin', 'statut', 'paiement_id'
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];

    public function apprenant()
    {
        return $this->belongsTo(User::class, 'apprenant_id');
    }

    public function type()
    {
        return $this->belongsTo(AbonnementType::class, 'type_abonnement_id');
    }

    public function paiement()
    {
        return $this->belongsTo(Paiement::class);
    }

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class);
    }

    // Vérifier si l'abonnement est actif
    public function isActif()
    {
        return $this->statut === 'actif' && Carbon::now()->lessThanOrEqualTo($this->date_fin);
    }

    // Vérifier si un cours est accessible via l'abonnement
    public function peutAccederCours($coursId)
    {
        return $this->isActif() && $this->type->cours->contains($coursId);
    }
}