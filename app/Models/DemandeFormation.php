<?php
// app/Models/DemandeFormation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeFormation extends Model
{
    protected $fillable = [
        'nom', 'email', 'telephone', 'titre_cours_souhaite',
        'description', 'domaine', 'niveau_souhaite', 'statut',
        'traite_le', 'traite_par', 'commentaire_admin'
    ];

    protected $casts = [
        'traite_le' => 'datetime',
    ];

    public function traitePar()
    {
        return $this->belongsTo(User::class, 'traite_par');
    }
}