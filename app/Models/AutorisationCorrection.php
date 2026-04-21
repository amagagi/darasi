<?php
// app/Models/AutorisationCorrection.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutorisationCorrection extends Model
{
    protected $table = 'autorisations_correction';
    
    protected $fillable = ['formateur_id', 'cours_id', 'autorise_par', 'date_autorisation', 'est_active'];

    protected $casts = [
        'date_autorisation' => 'datetime',
        'est_active' => 'boolean',
    ];

    public function formateur()
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    public function autorisePar()
    {
        return $this->belongsTo(User::class, 'autorise_par');
    }
}