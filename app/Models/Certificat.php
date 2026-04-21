<?php
// app/Models/Certificat.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificat extends Model
{
    protected $fillable = [
        'inscription_id', 'tentative_final_id', 'code_verification',
        'url_pdf', 'date_emission', 'est_valide',
        'date_revocation', 'revoque_par', 'motif_revocation'
    ];

    protected $casts = [
        'date_emission' => 'datetime',
        'date_revocation' => 'datetime',
        'est_valide' => 'boolean',
    ];

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function tentativeFinal()
    {
        return $this->belongsTo(TentativeTestFinal::class);
    }

    public function revoquePar()
    {
        return $this->belongsTo(User::class, 'revoque_par');
    }
}