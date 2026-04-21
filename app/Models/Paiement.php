<?php
// app/Models/Paiement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = [
        'apprenant_id', 'cours_id', 'abonnement_type_id',
        'montant', 'reference_komipay', 'statut',
        'mode_paiement', 'date_paiement'
    ];

    protected $casts = [
        'date_paiement' => 'datetime',
    ];

    public function apprenant()
    {
        return $this->belongsTo(User::class, 'apprenant_id');
    }

    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    public function abonnementType()
    {
        return $this->belongsTo(AbonnementType::class);
    }

    public function logs()
    {
        return $this->hasMany(PaiementLog::class);
    }

    public function abonnementSouscrit()
    {
        return $this->hasOne(AbonnementSouscrit::class);
    }
}