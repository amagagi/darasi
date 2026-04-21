<?php
// app/Models/Question.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['test_id', 'test_final_id', 'question', 'type', 'points', 'ordre'];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function testFinal()
    {
        return $this->belongsTo(TestFinal::class);
    }

    public function choix()
    {
        return $this->hasMany(ChoixQuestion::class)->orderBy('ordre');
    }

    public function reponses()
    {
        return $this->hasMany(ReponseQuestion::class);
    }
}