<?php
// app/Models/ConfigTentative.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigTentative extends Model
{
    protected $fillable = ['test_id', 'test_final_id', 'max_tentatives', 'delai_heures'];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function testFinal()
    {
        return $this->belongsTo(TestFinal::class);
    }
}