<?php
// app/Models/TestFinal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestFinal extends Model
{
    protected $fillable = ['cours_id', 'titre', 'description', 'note_minimale', 'duree_limite'];

    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function tentatives()
    {
        return $this->hasMany(TentativeTestFinal::class);
    }

    public function configTentative()
    {
        return $this->hasOne(ConfigTentative::class);
    }
}