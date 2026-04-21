<?php
// app/Models/Categorie.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    protected $fillable = ['pole_id', 'nom', 'description', 'slug', 'ordre'];

    public function pole()
    {
        return $this->belongsTo(Pole::class);
    }

    public function cours()
    {
        return $this->hasMany(Cours::class);
    }
}