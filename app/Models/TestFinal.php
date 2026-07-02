<?php
// app/Models/TestFinal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestFinal extends Model
{
    protected $table = 'tests_finaux';
    
    protected $fillable = ['cours_id', 'titre', 'description', 'note_minimale', 'duree_limite'];

    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'test_final_id');
    }

    // ✅ Correction : nom correct de la table
    public function tentatives()
    {
        return $this->hasMany(TentativeTestFinal::class, 'test_final_id');
    }

    public function configTentative()
    {
        return $this->hasOne(ConfigTentative::class, 'test_final_id');
    }
}