<?php
// app/Models/Module.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['cours_id', 'titre', 'description', 'ordre', 'duree_estimee'];

    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    public function lecons()
    {
        return $this->hasMany(Lecon::class)->orderBy('ordre');
    }

    public function test()
    {
        return $this->hasOne(Test::class);
    }
}