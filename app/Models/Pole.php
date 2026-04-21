<?php
// app/Models/Pole.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pole extends Model
{
    protected $fillable = ['nom', 'description', 'slug', 'ordre', 'is_active'];

    public function categories()
    {
        return $this->hasMany(Categorie::class);
    }

    public function niveaux()
    {
        return $this->hasMany(Niveau::class);
    }

    public function cours()
    {
        return $this->hasMany(Cours::class);
    }
}