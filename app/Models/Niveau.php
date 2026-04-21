<?php
// app/Models/Niveau.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Niveau extends Model
{
    protected $fillable = ['pole_id', 'libelle', 'description', 'ordre'];

    public function pole()
    {
        return $this->belongsTo(Pole::class);
    }

    public function cours()
    {
        return $this->hasMany(Cours::class);
    }
}