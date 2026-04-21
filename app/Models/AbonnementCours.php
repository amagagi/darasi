<?php
// app/Models/AbonnementCours.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbonnementCours extends Model
{
    protected $table = 'abonnements_cours';
    
    protected $fillable = ['abonnement_type_id', 'cours_id'];

    public function abonnementType()
    {
        return $this->belongsTo(AbonnementType::class);
    }

    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }
}