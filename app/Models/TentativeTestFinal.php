<?php
// app/Models/TentativeTestFinal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TentativeTestFinal extends Model
{
    protected $fillable = [
        'inscription_id', 'test_final_id', 'note', 'est_reussi',
        'tentative_numero', 'date_tentative', 'date_prochaine_autorisee',
        'a_obtenu_certificat', 'date_obtention_certificat'
    ];

    protected $casts = [
        'date_tentative' => 'datetime',
        'date_prochaine_autorisee' => 'datetime',
        'date_obtention_certificat' => 'datetime',
        'est_reussi' => 'boolean',
        'a_obtenu_certificat' => 'boolean',
    ];

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function testFinal()
    {
        return $this->belongsTo(TestFinal::class);
    }

    public function reponses()
    {
        return $this->hasMany(ReponseQuestion::class);
    }

    public function certificat()
    {
        return $this->hasOne(Certificat::class);
    }
}