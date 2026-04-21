<?php
// app/Models/Inscription.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inscription extends Model
{
    protected $fillable = [
        'apprenant_id', 'cours_id', 'progression',
        'tests_modules_valides', 'date_debut', 'date_completion',
        'statut', 'abonnement_id', 'est_via_abonnement'
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_completion' => 'datetime',
        'tests_modules_valides' => 'boolean',
        'est_via_abonnement' => 'boolean',
    ];

    public function apprenant()
    {
        return $this->belongsTo(User::class, 'apprenant_id');
    }

    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    public function abonnement()
    {
        return $this->belongsTo(AbonnementSouscrit::class);
    }

    public function progresLecons()
    {
        return $this->hasMany(ProgresLecon::class);
    }

    public function tentativesTests()
    {
        return $this->hasMany(TentativeTest::class);
    }

    public function tentativesTestFinal()
    {
        return $this->hasMany(TentativeTestFinal::class);
    }

    public function certificat()
    {
        return $this->hasOne(Certificat::class);
    }
}