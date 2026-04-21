<?php
// app/Models/ProgresLecon.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgresLecon extends Model
{
    protected $fillable = ['inscription_id', 'lecon_id', 'est_complete', 'temps_passe', 'date_completion'];

    protected $casts = [
        'est_complete' => 'boolean',
        'date_completion' => 'datetime',
    ];

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function lecon()
    {
        return $this->belongsTo(Lecon::class);
    }
}