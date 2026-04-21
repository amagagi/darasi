<?php
// app/Models/TentativeTest.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TentativeTest extends Model
{
    protected $fillable = [
        'inscription_id', 'test_id', 'note', 'est_valide',
        'tentative_numero', 'date_tentative', 'date_prochaine_autorisee'
    ];

    protected $casts = [
        'date_tentative' => 'datetime',
        'date_prochaine_autorisee' => 'datetime',
        'est_valide' => 'boolean',
    ];

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function reponses()
    {
        return $this->hasMany(ReponseQuestion::class);
    }
}