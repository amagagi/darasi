<?php
// app/Models/CorrectionOuverte.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrectionOuverte extends Model
{
    protected $fillable = ['reponse_id', 'note_accordee', 'commentaire', 'corrige_par', 'date_correction'];

    protected $casts = [
        'date_correction' => 'datetime',
    ];

    public function reponse()
    {
        return $this->belongsTo(ReponseQuestion::class);
    }

    public function correcteur()
    {
        return $this->belongsTo(User::class, 'corrige_par');
    }
}