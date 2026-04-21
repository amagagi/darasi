<?php
// app/Models/Test.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    protected $fillable = ['module_id', 'titre', 'description', 'ordre'];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function tentatives()
    {
        return $this->hasMany(TentativeTest::class);
    }

    public function configTentative()
    {
        return $this->hasOne(ConfigTentative::class);
    }
}