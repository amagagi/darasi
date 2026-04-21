<?php
// app/Models/ChoixQuestion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChoixQuestion extends Model
{
    protected $fillable = ['question_id', 'texte', 'est_correct', 'ordre'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}